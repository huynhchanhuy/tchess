<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Rook;
use Tchess\Entity\Piece\Queen;
use Tchess\Rule\MoveCheckerInterface;

class RookRules implements EventSubscriberInterface, MoveCheckerInterface
{
    public function checkMove(MoveEvent $event, $fromQueen = false)
    {
        $board = $event->getBoard();
        $move = $event->getMove();
        $piece = $board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());

        // not (rook or (queen and from-queen)).
        if (!$piece instanceof Rook && (!$piece instanceof Queen || !$fromQueen)) {
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
            MoveEvents::MOVE => array(array('onMoveRemoveCastlingAvailability', 0)),
        );
    }

    public static function getRules()
    {
        return array(array('checkMove', 0));
    }

}
