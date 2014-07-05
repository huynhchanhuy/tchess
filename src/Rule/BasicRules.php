<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Piece;

class BasicRules implements EventSubscriberInterface
{
    public function onMoveChecking(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();
        $color = $event->getColor();
        $source_piece = $board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        $target_piece = $board->getPiece($move->getNewRow(), $move->getNewColumn());

        if ($source_piece == null) {
            $event->setValidMove(false);
            $event->setMessage('There is no piece to move at ' . $move->getSource());
            return;
        }

        if (!$source_piece instanceof Piece || $source_piece->getColor() != $color) {
            $event->setValidMove(false);
            $event->setMessage('Can not move opponent piece at ' . $move->getSource());
            return;
        }

        if ($target_piece instanceof Piece && $target_piece->getColor() == $color) {
            $event->setValidMove(false);
            $event->setMessage('There is already your piece at ' . $move->getTarget());
            return;
        }

        $event->setValidMove(true);
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECH_MOVE => array(array('onMoveChecking', 1)),
        );
    }

}
