<?php

namespace Tchess\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Tchess\RoomEvents;
use Tchess\Event\RoomEvent;
use Tchess\Entity\Game;
use Tchess\Entity\Board;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Psr\Log\LoggerInterface;
use Tchess\MoveManager;
use Symfony\Component\HttpKernel\KernelEvents;

class GameListener implements EventSubscriberInterface
{

    private $em;
    private $serializer;
    private $logger;
    private $moveManager;
    private $socket;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, LoggerInterface $logger, MoveManager $moveManager)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->moveManager = $moveManager;
    }

    public function setSocket($socket = null)
    {
        $this->socket = $socket;
    }

    public function onRoomJoin(RoomEvent $event)
    {
        $room = $event->getRoom();

        if (count($room->getPlayers()) == 2) {
            $board = new Board();
            $board->initialize();

            $game = new Game();
            $game->setRoom($room);
            $game->setBoard($board);
            $game->saveGame($this->serializer);

            $this->em->persist($game);

            $room->setGame($game);
            $this->em->flush();
        }
    }

    public function onRoomLeave(RoomEvent $event)
    {
        $room = $event->getRoom();

        if (count($room->getPlayers()) == 0) {
            $this->em->remove($room);
            $this->em->flush();
        }
    }

    public function onKernelTerminateLogMoves(PostResponseEvent $event)
    {
        $moves = $this->moveManager->getMoves();

        if (empty($moves)) {
            return;
        }

        foreach ($moves as $move) {
            $this->logger->info(sprintf("Player '%s' has moved a piece from '%s' to '%s'", $move->getColor(), $move->getSource(), $move->getTarget()));
        }
    }

    public function onKernelTerminateBroadcastMoves(PostResponseEvent $event)
    {
        $moves = $this->moveManager->getMoves();

        if (empty($moves) || empty($this->socket)) {
            return;
        }

        foreach ($moves as $move) {
            $data = array(
                'room' => $move->getRoomId(),
                'action' => 'move',
                'source' => $move->getSource(),
                'target' => $move->getTarget(),
                'color' => $move->getColor(),
                'castling' => $move->getCastling(),
            );

            $this->socket->send(json_encode($data));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            RoomEvents::CREATE => array(array('onRoomCreate', 0)),
            // The second player join start game.
            RoomEvents::JOIN => array(array('onRoomJoin', 0)),
            // The last player leave delete room.
            RoomEvents::LEAVE => array(array('onRoomLeave', 0)),
            KernelEvents::TERMINATE => array('onKernelTerminateLogMoves', -1024),
            KernelEvents::TERMINATE => array('onKernelTerminateBroadcastMoves', -1024),
        );
    }

}
