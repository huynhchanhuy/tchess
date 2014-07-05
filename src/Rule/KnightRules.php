<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Knight;

class KnightRules implements EventSubscriberInterface
{
    public function onMoveChecking(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();
        $color = $event->getColor();
        $piece = $board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        if (!$piece instanceof Knight) {
            return;
        }

        if (abs($move->getNewRow() - $move->getCurrentRow()) == 2 && abs($move->getNewColumn() - $move->getCurrentColumn()) == 1) {
            $event->setValidMove(true);
            return;
        }

        if (abs($move->getNewRow() - $move->getCurrentRow()) == 1 && abs($move->getNewColumn() - $move->getCurrentColumn()) == 2) {
            $event->setValidMove(true);
            return;
        }

        $event->setValidMove(false);
        $event->stopPropagation();
        return;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECH_MOVE => array(array('onMoveChecking', 0)),
        );
    }

}
