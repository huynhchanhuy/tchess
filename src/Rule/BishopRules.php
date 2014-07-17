<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Bishop;
use Tchess\Entity\Board;
use Tchess\Entity\Piece\Move;
use Tchess\Rule\CheckingMoveInterface;

class BishopRules implements EventSubscriberInterface, CheckingMoveInterface
{
    public function onMoveChecking(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();
        $piece = $board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        if (!$piece instanceof Bishop) {
            return;
        }

        $valid = $this->checkMove($board, $move);
        $event->setValidMove($valid);
    }

    public function checkMove(Board $board, Move $move, $color = 'white')
    {
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
            MoveEvents::CHECH_MOVE => array(array('onMoveChecking', 0)),
        );
    }

}
