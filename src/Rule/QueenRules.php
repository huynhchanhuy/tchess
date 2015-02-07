<?php

namespace Tchess\Rule;

use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Queen;
use Tchess\Rule\MoveCheckerInterface;
use Tchess\Rule\BishopRules;
use Tchess\Rule\RookRules;

class QueenRules implements MoveCheckerInterface
{

    private $bishopRules;
    private $rookRules;

    public function __construct(BishopRules $bishopRules, RookRules $rookRules)
    {
        $this->bishopRules = $bishopRules;
        $this->rookRules = $rookRules;
    }

    public function checkMove(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();
        $piece = $board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        if (!$piece instanceof Queen) {
            return;
        }

        return $this->bishopRules->checkMove($event, true) || $this->rookRules->checkMove($event, true);
    }

    public static function getRules()
    {
        return array(array('checkMove', 0));
    }

}
