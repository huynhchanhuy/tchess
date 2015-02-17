<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\Entity\Piece\Bishop;
use Tchess\Entity\Piece\Queen;
use Tchess\Rule\MoveCheckerInterface;
use Tchess\Entity\Piece\Move;

class BishopRules implements EventSubscriberInterface, MoveCheckerInterface
{
    public function checkMove(Move $move, $fromQueen = false)
    {
        $board = $move->getBoard();
        list($currentRow, $currentColumn) = Move::getIndex($move->getSource());
        list($newRow, $newColumn) = Move::getIndex($move->getTarget());
        $piece = $board->getPiece($currentRow, $currentColumn);

        // not (bishop or (queen and from-queen)).
        if (!$piece instanceof Bishop && (!$piece instanceof Queen || !$fromQueen)) {
            return;
        }

        if (abs($newRow - $currentRow) != abs($newColumn - $currentColumn)) {
            return 'Did not move diagonally';
        }

        if ($currentRow < $newRow) {
            $rowOffset = 1;
        } else {
            $rowOffset = -1;
        }

        if ($currentColumn < $newColumn) {
            $colOffset = 1;
        } else {
            $colOffset = -1;
        }

        $y = $currentColumn + $colOffset;
        for($x = $currentRow + $rowOffset; $x != $newRow; $x += $rowOffset) {
            if($board->getPiece($x, $y) != null) {
                return 'Can not move through another piece';
            }

            $y += $colOffset;
        }
    }

    public static function getRules()
    {
        return array(array('checkMove', 0));
    }

    public static function getSubscribedEvents()
    {
        return array();
    }

}
