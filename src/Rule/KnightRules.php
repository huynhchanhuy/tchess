<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Knight;
use Tchess\Rule\CheckingMoveInterface;

class KnightRules implements EventSubscriberInterface, CheckingMoveInterface
{
    public function onMoveChecking(MoveEvent $event)
    {
        $valid = $this->checkMove($event);
        if (is_bool($valid)) {
          $event->setValidMove($valid);
        }
    }

    public function checkMove(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();
        $piece = $board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        if (!$piece instanceof Knight) {
            return;
        }

        if (abs($move->getNewRow() - $move->getCurrentRow()) == 2 && abs($move->getNewColumn() - $move->getCurrentColumn()) == 1) {
            return true;
        }

        if (abs($move->getNewRow() - $move->getCurrentRow()) == 1 && abs($move->getNewColumn() - $move->getCurrentColumn()) == 2) {
            return true;
        }

        return false;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECK_MOVE => array(array('onMoveChecking', 0)),
        );
    }

}
