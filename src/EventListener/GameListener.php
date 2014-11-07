<?php

namespace Tchess\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Tchess\GameEvents;
use Tchess\Event\GameEvent;
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

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, LoggerInterface $logger, MoveManager $moveManager)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->moveManager = $moveManager;
    }

    public function onGameStart(GameEvent $event)
    {
        $room = $event->getPlayer()->getRoom();
        $players = $room->getPlayers();

        if (count($players) != 2 || $players[0]->getColor() == $players[1]->getColor()) {
            return;
        }

        if ($players[0]->getStarted() && $players[1]->getStarted()) {
            $board = new Board();
            $board->initialize();

            $game = new Game();
            $game->setRoom($room);
            $game->setStarted(true);
            $game->setBoard($board);
            $game->saveGame($this->serializer);

            $this->em->persist($game);

            $room->setGame($game);
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

        if (empty($moves) || !(class_exists('\ZMQContext')) || !(class_exists('\ZMQ'))) {
            return;
        }

        $context = new \ZMQContext();
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://localhost:5555");

        foreach ($moves as $move) {
            $data = array(
                'source' => $move->getSource(),
                'target' => $move->getTarget(),
                'color' => $move->getColor(),
                'castling' => $move->getCastling(),
            );

            $socket->send(json_encode($data));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            GameEvents::START => array(array('onGameStart', 0)),
            KernelEvents::TERMINATE => array('onKernelTerminateLogMoves', -1024),
            KernelEvents::TERMINATE => array('onKernelTerminateBroadcastMoves', -1024),
        );
    }

}
