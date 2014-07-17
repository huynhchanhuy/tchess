<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Rook;
use Tchess\Entity\Board;
use Tchess\Entity\Piece\Move;
use Tchess\Rule\CheckingMoveInterface;

class RookRules implements EventSubscriberInterface, CheckingMoveInterface
{
    public function onMoveChecking(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();
        $piece = $board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        if (!$piece instanceof Rook) {
            return;
        }

        $valid = $this->checkMove($board, $move);
        $event->setValidMove($valid);
    }

    public function checkMove(Board $board, Move $move, $color = 'white')
    {
        if ($move->getCurrentRow() != $move->getNewRow() && $move->getCurrentColumn() != $move->getNewColumn()) {
            // Did not move along one rank/file.
            return false;
        }

        // First I will assumed the Rook is moving along the rows.
        if ($move->getCurrentRow() != $move->getNewRow()) {
            if ($move->getCurrentRow() < $move->getNewRow()) {
                $offset = 1;
            } else {
                $offset = -1;
            }

            for($x = $move->getCurrentRow() + $offset; $x != $move->getNewRow(); $x += $offset) {
                // Go from currentRow to newRow, and check every space.
                if($board->getPiece($x, $move->getCurrentColumn()) != null) {
                    return false;
                }
            }
        }

        // Now do the same for columns.
        if ($move->getCurrentColumn() != $move->getNewColumn()) {
            if ($move->getCurrentColumn() < $move->getNewColumn()) {
                $offset = 1;
            } else {
                $offset = -1;
            }

            for($x = $move->getCurrentColumn() + $offset; $x != $move->getNewColumn(); $x += $offset) {
                // Go from currentCol to newCol, and check every space.
                if($board->getPiece($move->getCurrentRow(), $x) != null) {
                    return false;
                }
            }
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
