<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Piece;
use Tchess\Rule\MoveCheckerInterface;

class BasicRules implements EventSubscriberInterface, MoveCheckerInterface
{
    public function checkMove(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();
        $color = $event->getColor();

        if ($move->getCurrentRow() == $move->getNewRow() && $move->getCurrentColumn() == $move->getNewColumn()) {
            return false;
        }

        $source_piece = $board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        $target_piece = $board->getPiece($move->getNewRow(), $move->getNewColumn());

        if ($source_piece == null) {
            return false;
        }

        if (!$source_piece instanceof Piece || $source_piece->getColor() != $color) {
            return false;
        }

        if ($target_piece instanceof Piece && $target_piece->getColor() == $color) {
            return false;
        }

        return true;
    }

    public function onMoveUpdateFullmoveNumber(MoveEvent $event)
    {
        $board = &$event->getBoard();
        $color = $event->getColor();
        if ($color == 'black') {
            // Incremented after Black's move.
            $board->increaseFullmoveNumber();
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::MOVE => array(array('onMoveUpdateFullmoveNumber', 0)),
        );
    }

    public static function getRules()
    {
        return array(array('checkMove', 1));
    }

}
