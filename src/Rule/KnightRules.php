<?php

namespace Tchess\Rule;

use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Knight;
use Tchess\Rule\MoveCheckerInterface;

class KnightRules implements MoveCheckerInterface
{
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

    public static function getRules()
    {
        return array(array('checkMove', 0));
    }

}
