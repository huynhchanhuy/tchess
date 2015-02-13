<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\Entity\Piece\Knight;
use Tchess\Rule\MoveCheckerInterface;
use Tchess\Entity\Piece\Move;

class KnightRules implements EventSubscriberInterface, MoveCheckerInterface
{
    public function checkMove(Move $move)
    {
        $board = $move->getBoard();
        list($currentRow, $currentColumn) = Move::getIndex($move->getSource());
        list($newRow, $newColumn) = Move::getIndex($move->getTarget());
        $piece = $board->getPiece($currentRow, $currentColumn);

        if (!$piece instanceof Knight) {
            return;
        }

        if (abs($newRow - $currentRow) == 2 && abs($newColumn - $currentColumn) == 1) {
            return true;
        }

        if (abs($newRow - $currentRow) == 1 && abs($newColumn - $currentColumn) == 2) {
            return true;
        }

        return false;
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
