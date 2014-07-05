<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\King;
use Tchess\Entity\Piece\Rook;

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

            if (abs($move->getNewColumn() - $move->getCurrentColumn()) != 2 || $move->getCurrentRow() != $move->getNewRow()) {
                $event->setValidMove(false);
                $event->stopPropagation();
                return;
            }

            // Do castling logic here.
            if ($move->getNewColumn() - $move->getCurrentColumn() == 2) {
                // Castle kingside.
                if ($board->getPiece($move->getNewRow(), $move->getCurrentColumn() + 1) != null || $board->getPiece($move->getNewRow(), $move->getCurrentColumn() + 2) != null) {
                    $piece->setCastled(false);
                    $event->setValidMove(false);
                    $event->stopPropagation();
                    return;
                }
            } else if ($move->getNewColumn() - $move->getCurrentColumn() == -2) {
                if ($board->getPiece($move->getNewRow(), $move->getCurrentColumn() - 1) != null || $board->getPiece($move->getNewRow(), $move->getCurrentColumn() - 2) != null) {
                    $piece->setCastled(false);
                    $event->setValidMove(false);
                    $event->stopPropagation();
                    return;
                }
            }

            // Just a flag indicate that this king will do a catling on the
            // actually move.
            $piece->setCastled(true);
        }

        // $piece->setHasMoved(true);
        $event->setValidMove(true);
    }

    public function onMoveDoCastling(MoveEvent $event)
    {
        $board = &$event->getBoard();
        $move = $event->getMove();
        $color = $event->getColor();
        $piece = &$board->getPiece($move->getNewRow(), $move->getNewColumn());
        if (!$piece instanceof King) {
            return;
        }

        if ($piece->isCastled()) {
            // Move rook.
            if ($move->getNewColumn() - $move->getCurrentColumn() == 2) {
                $rook = &$board->getPiece($move->getNewRow(), $move->getNewColumn() + 1);
                if (!$rook instanceof Rook || $rook->isMoved()) {
                    return;
                }

                // Kingside.
                $rook_move = new Move();
                $rook_move->setCurrentRow($move->getNewRow());
                $rook_move->setCurrentColumn($move->getNewColumn() + 1);
                $rook_move->setNewRow($move->getNewRow());
                $rook_move->setNewColumn($move->getNewColumn() - 1);

                $board->movePiece($rook_move);
            } else {
                $rook = &$board->getPiece($move->getNewRow(), $move->getNewColumn() - 2);
                if (!$rook instanceof Rook || $rook->isMoved()) {
                    return;
                }

                // Queenside.
                $rook_move = new Move();
                $rook_move->setCurrentRow($move->getNewRow());
                $rook_move->setCurrentColumn($move->getNewColumn() - 2);
                $rook_move->setNewRow($move->getNewRow());
                $rook_move->setNewColumn($move->getNewColumn() + 1);

                $board->movePiece($rook_move);
            }
            $piece->setCastled(false);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECH_MOVE => array(array('onMoveChecking', 0)),
            MoveEvents::MOVE => array(array('onMoveDoCastling', 0)),
        );
    }

}
