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
use Tchess\Rule\CheckingMoveInterface;
use Tchess\Entity\Piece\Piece;
use Tchess\Entity\Board;
use Tchess\Entity\Piece\Move;

class PawnRules implements EventSubscriberInterface, CheckingMoveInterface
{
    public function onMoveChecking(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();
        $color = $event->getColor();
        $piece = $board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        if (!$piece instanceof Pawn) {
            return;
        }

        $valid = $this->checkMove($board, $move, $color);
        $event->setValidMove($valid);
    }

    public function checkMove(Board $board, Move $move, $color = 'white')
    {
        $piece = $board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        if (!$piece instanceof Piece) {
            return;
        }

        if ($color == "white") {
            if ($move->getCurrentRow() >= $move->getNewRow()) {
                return false;
            }
        } else {
            if ($move->getNewRow() >= $move->getCurrentRow()) {
                return false;
            }
        }

        if ($move->getCurrentColumn() == $move->getNewColumn()) {
            // Not taking a piece.
            if ($color == "white") {
                if ($board->getPiece($move->getCurrentRow() + 1, $move->getCurrentColumn()) != null) {
                    return false;
                }
            } else {
                if ($board->getPiece($move->getCurrentRow() - 1, $move->getCurrentColumn()) != null) {
                    return false;
                }
            }

            if (abs($move->getNewRow() - $move->getCurrentRow()) > 2) {
                return false;
            } else if (abs($move->getNewRow() - $move->getCurrentRow()) == 2) {
                // Advancing two spaces at beginning.
                if ($piece->isMoved()) {
                    return false;
                }

                if ($piece->getColor() == 'white') {
                    if($board->getPiece($move->getCurrentRow() + 2, $move->getCurrentColumn()) != null) {
                        return false;
                    }
                } else {
                    if($board->getPiece($move->getCurrentRow() - 2, $move->getCurrentColumn()) != null) {
                        return false;
                    }
                }

                // En passante
                // @todo - Checking and doing en passante.
            }
        } else {
            // Taking a piece.
            if (abs($move->getNewColumn() - $move->getCurrentColumn()) != 1 || abs($move->getNewRow() - $move->getCurrentRow()) != 1) {
                return false;
            }

            if($board->getPiece($move->getNewRow(), $move->getNewColumn()) == null) {
                return false;
            }
        }

        return true;
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
                    // 96.9% people choose a queen for promotion.
                    $board->setPiece(new Queen($color), $move->getNewRow(), $move->getNewColumn());
                    break;

                case 'K':
                    // 1.8% people choose a knight for promotion.
                    $board->setPiece(new Knight($color), $move->getNewRow(), $move->getNewColumn());
                    break;

                case 'B':
                    // 1.1% people choose a bishop for promotion.
                    $board->setPiece(new Bishop($color), $move->getNewRow(), $move->getNewColumn());
                    break;

                case 'R':
                    // 0.2% people choose a rook for promotion.
                    $board->setPiece(new Rook($color), $move->getNewRow(), $move->getNewColumn());
                    break;

                default:
                    break;
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECK_MOVE => array(array('onMoveChecking', 0)),
            MoveEvents::MOVE => array(array('onMoveDoQueening', 0)),
        );
    }

}
