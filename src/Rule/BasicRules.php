<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;

class BasicRules implements EventSubscriberInterface
{
    public function onMoveChecking(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();
        $color = $event->getColor();

        if ($board->getPiece($move->getCurrentRow(), $move->getCurrentColumn()) == null) {
            throw new \LogicException('There is no piece to move at ' . $move->getSource());
        }

        if ($board->getPiece($move->getCurrentRow(), $move->getCurrentColumn())->getColor() != $color) {
            throw new \LogicException('Can not move opponent piece at ' . $move->getSource());
        }

        if ($board->getPiece($move->getNewRow(), $move->getNewColumn()) != null) {
            if ($board->getPiece($move->getNewRow(), $move->getNewColumn())->getColor() == $color) {
                throw new \LogicException('There is already your piece at ' . $move->getTarget());
            }
        }

        $event->setValidMove(true);
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECH_MOVE => array(array('onMoveChecking', 1)),
        );
    }

}
