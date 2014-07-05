<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\King;

class KingRules implements EventSubscriberInterface
{
    public function onMoveChecking(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();
        $color = $event->getColor();
        $piece = &$board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        if (!$piece instanceof King) {
            return;
        }


        if (abs($move->getNewRow() - $move->getCurrentRow()) > 1 || abs($move->getNewColumn() - $move->getCurrentColumn()) > 1) {

            if ($piece->isMoved()) {
                $event->setValidMove(false);
                $event->stopPropagation();
                return;
            }

            //Do castling logic here
            if ($move->getNewColumn() - $move->getCurrentColumn() == 2 && $move->getCurrentRow() == $move->getNewRow()) {
                //Castle kingside
                if($board->getPiece($move->getNewRow(), $move->getCurrentColumn() + 1) != null || $board->getPiece($move->getNewRow(), $move->getCurrentColumn() + 2) != null) {
                    $piece->setCastled(false);
                    $event->setValidMove(false);
                    $event->stopPropagation();
                    return;
                }
            } else if ($move->getCurrentColumn() - $move->getNewRow() == 3 && $move->getCurrentRow() == $move->getNewRow()) {
                if($board->getPiece($move->getNewRow(), $move->getCurrentColumn() - 1) != null || $board->getPiece($move->getNewRow(), $move->getCurrentColumn() - 2) != null || $board->getPiece($move->getNewRow(), $move->getCurrentColumn() - 3) != null) {
                    $piece->setCastled(false);
                    $event->setValidMove(false);
                    $event->stopPropagation();
                    return;
                }
            } else {
                $piece->setCastled(false);
                $event->setValidMove(false);
                $event->stopPropagation();
                return;
            }

            $piece->setCastled(true);
        }

        // $piece->setHasMoved(true);
        $event->setValidMove(true);
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECH_MOVE => array(array('onMoveChecking', 0)),
        );
    }

}
