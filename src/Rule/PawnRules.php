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
use Tchess\Rule\MoveCheckerInterface;
use Tchess\Entity\Piece\Piece;
use Tchess\Entity\Board;
use Tchess\Entity\Piece\Move;

class PawnRules implements EventSubscriberInterface, MoveCheckerInterface
{
    public function checkMove(MoveEvent $event)
    {
        $board = &$event->getBoard();
        $move = $event->getMove();
        $color = $event->getColor();

        $piece = &$board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        if (!$piece instanceof Pawn) {
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

                // En passant.
                if (in_array($move->getNewRow(), array(3, 4))) {
                    // Just for sure.
                    $piece->setEpAble(true);
                    $this->updateEnPassantTarget($board, $move, $piece);
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
                if ($color == 'white' && $move->getNewRow() == 5) {
                    $epPiece = $board->getPiece($move->getNewRow() - 1, $move->getNewColumn());
                }
                else if ($color == 'black' && $move->getNewRow() == 2) {
                    $epPiece = $board->getPiece($move->getNewRow() + 1, $move->getNewColumn());
                }
                if (!empty($epPiece) && $epPiece instanceof Pawn && $epPiece->getColor() != $color && $epPiece->isEpAble()) {
                    return true;
                }
                else {
                    return false;
                }
            }
        }

        return true;
    }

    private function updateEnPassantTarget(Board $board, Move $move, Piece $piece)
    {
        // Update en passant target.
        $newMove = new Move();
        $newMove->setNewColumn($move->getNewColumn());
        if ($piece->getColor() == 'white') {
            $newMove->setNewRow($move->getNewRow() - 1);
        }
        else {
            $newMove->setNewRow($move->getNewRow() + 1);
        }
        $board->setEnPassantTarget($newMove->getTarget());
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
        $piece = $board->getPiece($move->getNewRow(), $move->getNewColumn());

        if ($piece instanceof Pawn) {
            // Only pawn can capture en passant.
            if ($this->inSixthRank($color, $move->getNewRow())) {
                if ($move->getNewRow() == 2) {
                    if($board->getPiece($move->getNewRow() + 1, $move->getNewColumn()) != null) {
                        $epPiece = $board->getPiece($move->getNewRow() + 1, $move->getNewColumn());
                        if($epPiece instanceof Pawn && $epPiece->isEpAble() && $epPiece->getColor() != $color) {
                            $board->removePiece($move->getNewRow() + 1, $move->getNewColumn());
                        }
                    }
                } else if ($move->getNewRow() == 5) {
                    if($board->getPiece($move->getNewRow() - 1, $move->getNewColumn()) != null) {
                        $epPiece = $board->getPiece($move->getNewRow() - 1, $move->getNewColumn());
                        if($epPiece instanceof Pawn && $epPiece->isEpAble() && $epPiece->getColor() != $color) {
                            $board->removePiece($move->getNewRow() - 1, $move->getNewColumn());
                        }
                    }
                }
            }
        }

        // You only get one chance to capture en passant.
        $enPassantCount = 0;
        for ($row = 3; $row <= 4; $row++) {
            for ($column = 0; $column < 8; $column++) {
                if($board->getPiece($row, $column) != null) {
                    $epPiece = &$board->getPiece($row, $column);

                    // Disable en passant able on opponent pawns.
                    if($epPiece instanceof Pawn && $epPiece->isEpAble() && $epPiece->getColor() != $color) {
                        // Actually, we can just check column 4 for white, or
                        // column 3 for black.
                        $epPiece->setEpAble(false);
                    }

                    // Check if there is still an en passant target.
                    if ($epPiece instanceof Pawn && $epPiece->isEpAble()) {
                        $enPassantCount++;
                    }
                }
            }
        }

        if ($enPassantCount == 0) {
            $board->setEnPassantTarget('');
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::MOVE => array(
                array('onMoveDoQueening', 0),
                array('onMoveCaptureEnPassant', 0),
            ),
        );
    }

    public static function getRules()
    {
        return array(array('checkMove', 0));
    }

}
