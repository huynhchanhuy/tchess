<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Bishop;
use Tchess\Rule\CheckingMoveInterface;

class BishopRules implements EventSubscriberInterface, CheckingMoveInterface
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
        if (!$piece instanceof Bishop) {
            return;
        }

        if ($move->getCurrentRow() == $move->getNewRow() || $move->getCurrentColumn() == $move->getNewColumn()) {
            // Did not move diagonally.
            return false;
        }

        if (abs($move->getNewRow() - $move->getCurrentRow()) != abs($move->getNewColumn() - $move->getCurrentColumn())) {
            return false;
        }

        if ($move->getCurrentRow() < $move->getNewRow()) {
            $rowOffset = 1;
        } else {
            $rowOffset = -1;
        }

        if ($move->getCurrentColumn() < $move->getNewColumn()) {
            $colOffset = 1;
        } else {
            $colOffset = -1;
        }

        $y = $move->getCurrentColumn() + $colOffset;
        for($x = $move->getCurrentRow() + $rowOffset; $x != $move->getNewRow(); $x += $rowOffset) {
            if($board->getPiece($x, $y) != null) {
                return false;
            }

            $y += $colOffset;
        }

        return true;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECK_MOVE => array(array('onMoveChecking', 0)),
        );
    }

}
