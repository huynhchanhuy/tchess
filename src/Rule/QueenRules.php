<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Queen;
use Tchess\Rule\CheckingMoveInterface;
use Tchess\Rule\BishopRules;
use Tchess\Rule\RookRules;

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
        $valid = $this->checkMove($event);
        if (is_bool($valid)) {
          $event->setValidMove($valid);
        }
    }

    public function checkMove(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();
        $piece = $board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        if (!$piece instanceof Queen) {
            return;
        }

        return $this->bishopRules->checkMove($event) || $this->rookRules->checkMove($event);
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECK_MOVE => array(array('onMoveChecking', 0)),
        );
    }

}
