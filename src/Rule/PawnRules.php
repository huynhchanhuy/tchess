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
    public function checkMove(Move $move)
    {
        $board = $move->getBoard();
        list($currentRow, $currentColumn) = Move::getIndex($move->getSource());
        list($newRow, $newColumn) = Move::getIndex($move->getTarget());
        $color = $move->getColor();
        $piece = $board->getPiece($currentRow, $currentColumn);

        if (!$piece instanceof Pawn) {
            return;
        }

        if ($color == "white") {
            if ($currentRow >= $newRow) {
                return 'Can not move a pawn backward';
            }
        } else {
            if ($newRow >= $currentRow) {
                return 'Can not move a pawn backward';
            }
        }

        if ($currentColumn == $newColumn) {
            if ($color == "white") {
                if ($board->getPiece($currentRow + 1, $currentColumn) != null) {
                    return 'A pawn can not take a piece in front of it';
                }
            } else {
                if ($board->getPiece($currentRow - 1, $currentColumn) != null) {
                    return 'A pawn can not take a piece in front of it';
                }
            }

            if (abs($newRow - $currentRow) > 2) {
                return 'Can not move more than 2 rows';
            } else if (abs($newRow - $currentRow) == 2) {
                // Advancing two spaces at beginning.
                if ($piece->isMoved()) {
                    return 'Can not move 2 rows if the pawn is moved';
                }

                if ($piece->getColor() == 'white') {
                    if($board->getPiece($currentRow + 2, $currentColumn) != null) {
                        return 'Can not take a piece while advancing 2 rows at beginning';
                    }
                } else {
                    if($board->getPiece($currentRow - 2, $currentColumn) != null) {
                        return 'Can not take a piece while advancing 2 rows at beginning';
                    }
                }

                // En passant.
                if (in_array($newRow, array(3, 4))) {
                    // Just for sure.
                    $piece->setEpAble(true);
                    $this->updateEnPassantTarget($board, $move, $piece);
                }
            } else if (abs($newRow - $currentRow) == 1) {
                // Allowed to move.
            }
        } else {
            // Taking a piece.
            if (abs($newColumn - $currentColumn) != 1 || abs($newRow - $currentRow) != 1) {
                return 'Invalid taking piece move';
            }

            if($board->getPiece($newRow, $newColumn) == null) {
                // Capture en passant.
                if ($color == 'white' && $newRow == 5) {
                    $epPiece = $board->getPiece($newRow - 1, $newColumn);
                }
                else if ($color == 'black' && $newRow == 2) {
                    $epPiece = $board->getPiece($newRow + 1, $newColumn);
                }
                if (empty($epPiece) || !$epPiece instanceof Pawn || $epPiece->getColor() == $color || !$epPiece->isEpAble()) {
                    return 'Invalid capture en passant move';
                }
            }
        }
    }

    private function updateEnPassantTarget(Board $board, Move $move, Piece $piece)
    {
        // Update en passant target.
        list($newRow, $newColumn) = Move::getIndex($move->getTarget());

        if ($piece->getColor() == 'white') {
            $newRow = $newRow - 1;
        }
        else {
            $newRow = $newRow + 1;
        }

        $target = Move::getSquare($newRow, $newColumn);
        $board->setEnPassantTarget($target);
    }

    public function onMoveDoQueening(MoveEvent $event)
    {
        $move = $event->getMove();
        $board = $move->getBoard();
        $color = $move->getColor();
        list($newRow, $newColumn) = Move::getIndex($move->getTarget());
        $piece = $board->getPiece($newRow, $newColumn);

        if (!$piece instanceof Pawn) {
            return;
        }

        if (($color == 'white' && $newRow == 7) || ($color == 'black' && $newRow == 0)) {
            switch ($move->getPromotion()) {
                case 'Q':
                    // 96.9% people choose a queen for promotion.
                    $board->setPiece(new Queen($color), $newRow, $newColumn);
                    break;

                case 'K':
                    // 1.8% people choose a knight for promotion.
                    $board->setPiece(new Knight($color), $newRow, $newColumn);
                    break;

                case 'B':
                    // 1.1% people choose a bishop for promotion.
                    $board->setPiece(new Bishop($color), $newRow, $newColumn);
                    break;

                case 'R':
                    // 0.2% people choose a rook for promotion.
                    $board->setPiece(new Rook($color), $newRow, $newColumn);
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
        $move = $event->getMove();
        $board = $move->getBoard();
        $color = $move->getColor();
        list($newRow, $newColumn) = Move::getIndex($move->getTarget());
        $piece = $board->getPiece($newRow, $newColumn);

        if ($piece instanceof Pawn) {
            // Only pawn can capture en passant.
            if ($this->inSixthRank($color, $newRow)) {
                if ($newRow == 2) {
                    if($board->getPiece($newRow + 1, $newColumn) != null) {
                        $epPiece = $board->getPiece($newRow + 1, $newColumn);
                        if($epPiece instanceof Pawn && $epPiece->isEpAble() && $epPiece->getColor() != $color) {
                            $board->removePiece($newRow + 1, $newColumn);
                        }
                    }
                } else if ($newRow == 5) {
                    if($board->getPiece($newRow - 1, $newColumn) != null) {
                        $epPiece = $board->getPiece($newRow - 1, $newColumn);
                        if($epPiece instanceof Pawn && $epPiece->isEpAble() && $epPiece->getColor() != $color) {
                            $board->removePiece($newRow - 1, $newColumn);
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
                    $epPiece = $board->getPiece($row, $column);

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
