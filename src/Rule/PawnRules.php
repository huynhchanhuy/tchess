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
                if ($move->getNewColumn() + 1 < 8) {
                    // Check right side.
                    if($board->getPiece($move->getNewRow(), $move->getNewColumn() + 1) != null) {
                        $epPiece = &$board->getPiece($move->getNewRow(), $move->getNewColumn() + 1);
                        if($epPiece instanceof Pawn && $epPiece->getColor() != $color) {
                            $epPiece->setEpAble(true);
                        }
                    }
                } else if ($move->getNewColumn() - 1 > 0) {
                    // Check left side.
                    if($board->getPiece($move->getNewRow(), $move->getNewColumn() - 1) != null) {
                        $epPiece = &$board->getPiece($move->getNewRow(), $move->getNewColumn() - 1);
                        if($epPiece instanceof Pawn && $epPiece->getColor() != $color) {
                            $epPiece->setEpAble(true);
                        }
                    }
                }
            } else if (abs($move->getNewRow() - $move->getCurrentRow()) == 1) {
                // Allowed to move.
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

    private function inSixthRank($color, $rank){
        if ($color == 'white') {
            return $rank == 5;
        } else {
            return $rank == 2;
        }
    }

    public function onMoveCaptureEnPassant(MoveEvent $event)
    {
        $board = &$event->getBoard();
        $move = $event->getMove();
        $color = $event->getColor();
        $piece = &$board->getPiece($move->getNewRow(), $move->getNewColumn());
        if (!$piece instanceof Pawn) {
            return;
        }

        if ($this->inSixthRank($color, $move->getNewRow())) {
            if ($move->getNewRow() == 2) {
                if($board->getPiece($move->getNewRow() + 1, $move->getNewColumn()) != null) {
                    $epPiece = $board->getPiece($move->getNewRow() + 1, $move->getNewColumn());
                    if($epPiece instanceof Pawn && $epPiece->isEpAble() && $epPiece->getColor() != $color) {
                        $board->setPiece($move->getNewRow() + 1, $move->getNewColumn(), null);
                    }
                }
            } else if ($move->getNewRow() == 5) {
                if($board->getPiece($move->getNewRow() - 1, $move->getNewColumn()) != null) {
                    $epPiece = $board->getPiece($move->getNewRow() - 1, $move->getNewColumn());
                    if($epPiece instanceof Pawn && $epPiece->isEpAble() && $epPiece->getColor() != $color) {
                        $board->setPiece($move->getNewRow() - 1, $move->getNewColumn(), null);
                    }
                }
            }
        }

        // You only get one chance to capture en passant.
        if ($color == 'white') {
            $row = 4;
        } else {
            $row = 3;
        }
        for ($column = 0; $column < 8; $column++) {
            if($board->getPiece($row, $column) != null) {
                $epPiece = &$board->getPiece($row, $column);
                if($epPiece instanceof Pawn && $epPiece->isEpAble() && $epPiece->getColor() != $color) {
                    $epPiece->setEpAble(false);
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECK_MOVE => array(array('onMoveChecking', 0)),
            MoveEvents::MOVE => array(
                array('onMoveDoQueening', 0),
                array('onMoveCaptureEnPassant', 0),
            ),
        );
    }

}
