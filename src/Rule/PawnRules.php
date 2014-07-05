<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Pawn;

class PawnRules implements EventSubscriberInterface
{
    public function onMoveChecking(MoveEvent $event)
    {
        $board = $event->getGame()->getBoard();
        $move = $event->getMove();
        $color = $event->getColor();
        $piece = $board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        if (!$piece instanceof Pawn) {
            return;
        }

        if ($color == "white") {
            if ($move->getCurrentRow() > $move->getNewRow()) {
                $event->setValidMove(false);
                $event->stopPropagation();
                return;
            }
        } else {
            if ($move->getNewRow() > $move->getCurrentRow()) {
                $event->setValidMove(false);
                $event->stopPropagation();
                return;
            }
        }

        if ($move->getCurrentColumn() == $move->getNewColumn()) {
            //Not taking a piece
            if ($color == "white") {
                if ($board->getPiece($move->getCurrentRow() + 1, $move->getCurrentColumn()) != null) {
                    $event->setValidMove(false);
                    $event->stopPropagation();
                    return;
                }
            } else {
                if ($board->getPiece($move->getCurrentRow() - 1, $move->getCurrentColumn()) != null) {
                    $event->setValidMove(false);
                    $event->stopPropagation();
                    return;
                }
            }

            if (abs($move->getNewRow() - $move->getCurrentRow()) > 2) {
                $event->setValidMove(false);
                $event->stopPropagation();
                return;
            } else if (abs($move->getNewRow() - $move->getCurrentRow()) == 2) {
                //Advancing two spaces at beginning
                if ($piece->isMoved()) {
                    $event->setValidMove(false);
                    $event->stopPropagation();
                    return;
                }

                if ($piece->getColor() == 'white') {
                    if($board->getPiece($move->getCurrentRow() + 2, $move->getCurrentColumn()) != null) {
                        $event->setValidMove(false);
                        $event->stopPropagation();
                        return;
                    }
                } else {
                    if($board->getPiece($move->getCurrentRow() - 2, $move->getCurrentColumn()) != null) {
                        $event->setValidMove(false);
                        $event->stopPropagation();
                        return;
                    }
                }

                // En passante
                // We do not check this rule. It's too complex.
            }
        } else {
            //Taking a piece
            if (abs($move->getNewColumn() - $move->getCurrentColumn()) != 1 || abs($move->getNewRow() - $move->getCurrentRow()) != 1) {
                $event->setValidMove(false);
                $event->stopPropagation();
                return;
            }

            if($board->getPiece($move->getNewRow(), $move->getNewColumn()) == null) {
                $event->setValidMove(false);
                $event->stopPropagation();
                return;
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
