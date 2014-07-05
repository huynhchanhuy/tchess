<?php

namespace Tchess\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Tchess\GameEvents;
use Tchess\Event\GameEvent;
use Tchess\Entity\Game;

class GameListener implements EventSubscriberInterface
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onGameStart(GameEvent $event)
    {
        $room = $event->getPlayer()->getRoom();
        $players = $room->getPlayers();

        if (count($players) != 2 || $players[0]->getColor() == $players[1]->getColor()) {
            return;
        }

        if ($players[0]->getStarted() && $players[1]->getStarted()) {
            $game = new Game();
            $game->setTurn('white');
            $game->setRoom($room);
            $game->setStarted(true);
            $game->setState('');
            $this->em->persist($game);

            $room->setGame($game);
            $this->em->flush();
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            GameEvents::START => array(array('onGameStart', 0)),
        );
    }

}
