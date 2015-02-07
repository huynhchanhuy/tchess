<?php

namespace Tchess\Rule;

use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Bishop;
use Tchess\Entity\Piece\Queen;
use Tchess\Rule\MoveCheckerInterface;

class BishopRules implements MoveCheckerInterface
{
    public function checkMove(MoveEvent $event, $fromQueen = false)
    {
        $board = $event->getBoard();
        $move = $event->getMove();

        $piece = $board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());

        // not (bishop or (queen and from-queen)).
        if (!$piece instanceof Bishop && (!$piece instanceof Queen || !$fromQueen)) {
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

    public static function getRules()
    {
        return array(array('checkMove', 0));
    }

}
