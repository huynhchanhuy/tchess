<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\Entity\Piece\Queen;
use Tchess\Rule\MoveCheckerInterface;
use Tchess\Rule\BishopRules;
use Tchess\Rule\RookRules;
use Tchess\Entity\Piece\Move;

class QueenRules implements EventSubscriberInterface, MoveCheckerInterface
{

    private $bishopRules;
    private $rookRules;

    public function __construct(BishopRules $bishopRules, RookRules $rookRules)
    {
        $this->bishopRules = $bishopRules;
        $this->rookRules = $rookRules;
    }

    public function checkMove(Move $move)
    {
        $board = $move->getBoard();
        list($currentRow, $currentColumn) = Move::getIndex($move->getSource());
        $piece = $board->getPiece($currentRow, $currentColumn);

        if (!$piece instanceof Queen) {
            return;
        }

        return $this->bishopRules->checkMove($move, true) || $this->rookRules->checkMove($move, true);
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
