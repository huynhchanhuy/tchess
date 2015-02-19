<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Rook;
use Tchess\Entity\Piece\Queen;
use Tchess\Rule\MoveCheckerInterface;
use Tchess\Entity\Piece\Move;

class RookRules implements EventSubscriberInterface, MoveCheckerInterface
{
    public function checkMove(Move $move, $fromQueen = false)
    {
        $board = $move->getBoard();
        list($currentRow, $currentColumn) = Move::getIndex($move->getSource());
        list($newRow, $newColumn) = Move::getIndex($move->getTarget());
        $piece = $board->getPiece($currentRow, $currentColumn);

        // not (rook or (queen and from-queen)).
        if (!$piece instanceof Rook && (!$piece instanceof Queen || !$fromQueen)) {
            return;
        }

        if ($currentRow != $newRow && $currentColumn != $newColumn) {
            return 'Did not move along one row/column';
        }

        // First I will assumed the Rook is moving along the rows.
        if ($currentRow != $newRow) {
            if ($currentRow < $newRow) {
                $offset = 1;
            } else {
                $offset = -1;
            }

            for($x = $currentRow + $offset; $x != $newRow; $x += $offset) {
                // Go from currentRow to newRow, and check every space.
                if($board->getPiece($x, $currentColumn) != null) {
                    return 'There is a piece between source and target squares';
                }
            }
        }

        // Now do the same for columns.
        if ($currentColumn != $newColumn) {
            if ($currentColumn < $newColumn) {
                $offset = 1;
            } else {
                $offset = -1;
            }

            for($x = $currentColumn + $offset; $x != $newColumn; $x += $offset) {
                // Go from currentCol to newCol, and check every space.
                if($board->getPiece($currentRow, $x) != null) {
                    return 'There is a piece between source and target squares';
                }
            }
        }
    }

    public function onMoveRemoveCastlingAvailability(MoveEvent $event)
    {
        $move = $event->getMove();
        $board = $move->getBoard();
        $color = $move->getColor();
        list($currentRow, $currentColumn) = Move::getIndex($move->getSource());
        list($newRow, $newColumn) = Move::getIndex($move->getTarget());
        $piece = $board->getPiece($newRow, $newColumn);
        if (!$piece instanceof Rook) {
            return;
        }

        if ($currentColumn == 0) {
            // Queenside.
            $board->removeCastlingAvailability($color == 'white' ? 'Q' : 'q');
        } elseif ($currentColumn == 7) {
            // Kingside.
            $board->removeCastlingAvailability($color == 'white' ? 'K' : 'k');
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::MOVE => array(
                array('onMoveRemoveCastlingAvailability', 0),
            ),
        );
    }

    public static function getRules()
    {
        return array(array('checkMove', 0));
    }

}
