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
use Tchess\MessageManager;
use Symfony\Component\HttpKernel\KernelEvents;

class GameListener implements EventSubscriberInterface
{

    private $em;
    private $serializer;
    private $logger;
    private $messageManager;
    private $socket;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, LoggerInterface $logger, MessageManager $messageManager)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
    }

    public function setSocket($socket = null)
    {
        $this->socket = $socket;
    }

    public function onRoomJoin(RoomEvent $event)
    {
        $room = $event->getRoom();
        $player = $event->getPlayer();

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

        $message = new Message($room->getId(), 'join', array(
            'color' => $player->getColor(),
            'name' => $player->getName(),
        ));
        $this->messageManager->addMessage($message);
    }

    public function onRoomLeave(RoomEvent $event)
    {
        $room = $event->getRoom();
        $player = $event->getPlayer();

        if (count($room->getPlayers()) == 0) {
            $this->em->remove($room);
            $this->em->flush();
        }

        $message = new Message($room->getId(), 'join', array(
            'color' => $player->getColor(),
        ));
        $this->messageManager->addMessage($message);
    }

    public function onKernelTerminateLogMoves(PostResponseEvent $event)
    {
        $messages = $this->messageManager->getMessages();

        if (empty($messages)) {
            return;
        }

        foreach ($messages as $message) {
            if ($message->getAction() == 'move') {
                $data = $message->getData();
                $this->logger->info(sprintf("Player '%s' has moved a piece from '%s' to '%s'", $data['color'], $data['source'], $data['target']));
            }
        }
    }

    public function onKernelTerminateBroadcastMoves(PostResponseEvent $event)
    {
        $messages = $this->messageManager->getMessages();

        if (empty($messages) || empty($this->socket)) {
            return;
        }

        foreach ($messages as $message) {
            $this->socket->send(json_encode($message));
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
