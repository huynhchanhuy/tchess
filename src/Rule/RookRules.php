<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Rook;
use Tchess\Entity\Piece\Queen;
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
        if (!$piece instanceof Rook && !$piece instanceof Queen) {
            return;
        }

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

    public function onMoveRemoveCastlingAvailability(MoveEvent $event)
    {
        $board = &$event->getBoard();
        $move = $event->getMove();
        $color = $event->getColor();
        $piece = &$board->getPiece($move->getNewRow(), $move->getNewColumn());
        if (!$piece instanceof Rook) {
            return;
        }

        if ($move->getCurrentColumn() == 0) {
            // Queenside.
            $board->removeCastlingAvailability($color == 'white' ? 'Q' : 'q');
        } elseif ($move->getCurrentColumn() == 7) {
            // Kingside.
            $board->removeCastlingAvailability($color == 'white' ? 'K' : 'k');
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECK_MOVE => array(array('onMoveChecking', 0)),
            MoveEvents::MOVE => array(array('onMoveRemoveCastlingAvailability', 0)),
        );
    }

}
