<?php

namespace Tchess\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Tchess\GameEvents;
use Tchess\Event\GameEvent;
use Tchess\Entity\Game;

class GameListener implements EventSubscriberInterface
{

    public function onGameStart(GameEvent $event)
    {
        $room = $event->getPlayer()->getRoom();
        $players = $room->getPlayers();

        if (count($players) != 2 || $players[0]->getColor() == $players[1]->getColor()) {
            return;
        }

        if ($players[0]->getStarted() && $players[1]->getStarted()) {
            $game = new Game();
            $game->setCurrentTurn('white');
            $game->setRoom($room);
            // @todo - Need update init board state here.
            $game->setState('');
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            GameEvents::START => array(array('onGameStart', 0)),
        );
    }

}
