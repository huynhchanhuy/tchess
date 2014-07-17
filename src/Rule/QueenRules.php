<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Queen;
use Tchess\Rule\CheckingMoveInterface;
use Tchess\Rule\BishopRules;
use Tchess\Rule\RookRules;
use Tchess\Entity\Board;
use Tchess\Entity\Piece\Move;

class QueenRules implements EventSubscriberInterface, CheckingMoveInterface
{

    private $rook_rules;
    private $bishop_rules;

    public function __construct(BishopRules $rook_rules, RookRules $bishop_rules)
    {
        $this->rook_rules = $rook_rules;
        $this->bishop_rules = $bishop_rules;
    }

    public function onMoveChecking(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();
        $piece = $board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        if (!$piece instanceof Queen) {
            return;
        }

        $valid = $this->checkMove($board, $move);
        $event->setValidMove($valid);
    }

    public function checkMove(Board $board, Move $move, $color = 'white')
    {
        return $this->bishop_rules->checkMove($board, $move, $color) || $this->rook_rules->checkMove($board, $move, $color);
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECH_MOVE => array(array('onMoveChecking', 0)),
        );
    }

}
