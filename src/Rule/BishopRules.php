<?php

namespace Tchess\Rule;

use Tchess\Entity\Piece\Bishop;
use Tchess\Entity\Piece\Queen;
use Tchess\Rule\MoveCheckerInterface;
use Tchess\Entity\Piece\Move;

class BishopRules implements MoveCheckerInterface
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

        if ($currentRow == $newRow || $currentColumn == $newColumn) {
            // Did not move diagonally.
            return false;
        }

        if (abs($newRow - $currentRow) != abs($newColumn - $currentColumn)) {
            return false;
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
                return false;
            }

            $y += $colOffset;
        }

        return true;
    }

    public static function getRules()
    {
        return array(array('checkMove', 0));
    }

}
