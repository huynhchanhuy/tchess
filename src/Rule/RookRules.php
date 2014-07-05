<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Bishop;
use Tchess\Entity\Piece\Queen;

class BishopRules implements EventSubscriberInterface
{
    public function onMoveChecking(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();
        $color = $event->getColor();
        $piece = $board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        if (!$piece instanceof Bishop && !$piece instanceof Queen) {
            return;
        }

        if ($move->getCurrentRow() != $move->getNewRow() && $move->getCurrentColumn() != $move->getNewColumn()) {
            //Did not move along one rank/file
            $event->setValidMove(false);
            $event->stopPropagation();
            return;
        }

        //First I will assumed the Rook is moving along the rows.
        if ($move->getCurrentRow() != $move->getNewRow()) {
            if ($move->getCurrentRow() < $move->getNewRow()) {
                $offset = 1;
            } else {
                $offset = -1;
            }

            for($x = $move->getCurrentRow() + $offset; $x != $move->getNewRow(); $x += $offset) {
                //Go from currentRow to newRow, and check every space
                if($board->getPiece($x, $move->getCurrentColumn()) != null) {
                    $event->setValidMove(false);
                    $event->stopPropagation();
                    return;
                }
            }
        }

        //Now do the same for columns
        if ($move->getCurrentColumn() != $move->getNewColumn()) {
            if ($move->getCurrentColumn() < $move->getNewColumn()) {
                $offset = 1;
            } else {
                $offset = -1;
            }

            for($x = $move->getCurrentColumn() + $offset; $x != $move->getNewColumn(); $x += $offset) {
                //Go from currentCol to newCol, and check every space
                if($board->getPiece($move->getCurrentRow(), $x) != null) {
                    $event->setValidMove(false);
                    $event->stopPropagation();
                    return;
                }
            }
        }

        $event->setValidMove(true);
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECH_MOVE => array(array('onMoveChecking', 0)),
        );
    }

}
