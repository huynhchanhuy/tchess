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

    private $bishopRules;
    private $rookRules;

    public function __construct(BishopRules $bishopRules, RookRules $rookRules)
    {
        $this->bishopRules = $bishopRules;
        $this->rookRules = $rookRules;
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
        return $this->bishopRules->checkMove($board, $move, $color) || $this->rookRules->checkMove($board, $move, $color);
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECK_MOVE => array(array('onMoveChecking', 0)),
        );
    }

}
