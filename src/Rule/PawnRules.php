<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Pawn;
use Tchess\Entity\Piece\Queen;
use Tchess\Entity\Piece\Knight;
use Tchess\Entity\Piece\Bishop;
use Tchess\Entity\Piece\Rook;

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
            if ($move->getCurrentRow() >= $move->getNewRow()) {
                $event->setValidMove(false);
                return;
            }
        } else {
            if ($move->getNewRow() >= $move->getCurrentRow()) {
                $event->setValidMove(false);
                return;
            }
        }

        if ($move->getCurrentColumn() == $move->getNewColumn()) {
            // Not taking a piece.
            if ($color == "white") {
                if ($board->getPiece($move->getCurrentRow() + 1, $move->getCurrentColumn()) != null) {
                    $event->setValidMove(false);
                    return;
                }
            } else {
                if ($board->getPiece($move->getCurrentRow() - 1, $move->getCurrentColumn()) != null) {
                    $event->setValidMove(false);
                    return;
                }
            }

            if (abs($move->getNewRow() - $move->getCurrentRow()) > 2) {
                $event->setValidMove(false);
                return;
            } else if (abs($move->getNewRow() - $move->getCurrentRow()) == 2) {
                // Advancing two spaces at beginning.
                if ($piece->isMoved()) {
                    $event->setValidMove(false);
                    return;
                }

                if ($piece->getColor() == 'white') {
                    if($board->getPiece($move->getCurrentRow() + 2, $move->getCurrentColumn()) != null) {
                        $event->setValidMove(false);
                        return;
                    }
                } else {
                    if($board->getPiece($move->getCurrentRow() - 2, $move->getCurrentColumn()) != null) {
                        $event->setValidMove(false);
                        return;
                    }
                }

                // En passante
                // @todo - Checking and doing en passante.
            }
        } else {
            // Taking a piece.
            if (abs($move->getNewColumn() - $move->getCurrentColumn()) != 1 || abs($move->getNewRow() - $move->getCurrentRow()) != 1) {
                $event->setValidMove(false);
                return;
            }

            if($board->getPiece($move->getNewRow(), $move->getNewColumn()) == null) {
                $event->setValidMove(false);
                return;
            }
        }

        $event->setValidMove(true);
    }

    public function onMoveDoQueening(MoveEvent $event)
    {
        $board = &$event->getBoard();
        $move = $event->getMove();
        $color = $event->getColor();
        $piece = &$board->getPiece($move->getNewRow(), $move->getNewColumn());
        if (!$piece instanceof Pawn) {
            return;
        }

        if (($color == 'white' && $move->getNewRow() == 7) || ($color == 'black' && $move->getNewRow() == 0)) {
            switch ($move->getPromotion()) {
                case 'Q':
                    $board->setPiece(new Queen('white'), $move->getNewRow(), $move->getNewColumn());
                    break;

                case 'K':
                    $board->setPiece(new Knight('white'), $move->getNewRow(), $move->getNewColumn());
                    break;

                case 'B':
                    $board->setPiece(new Bishop('white'), $move->getNewRow(), $move->getNewColumn());
                    break;

                case 'R':
                    $board->setPiece(new Rook('white'), $move->getNewRow(), $move->getNewColumn());
                    break;

                default:
                    break;
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECH_MOVE => array(array('onMoveChecking', 0)),
            MoveEvents::MOVE => array(array('onMoveDoQueening', 0)),
        );
    }

}
