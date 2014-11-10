<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\King;
use Tchess\Entity\Piece\Rook;
use Tchess\Entity\Piece\Move;
use Tchess\Rule\CheckingMoveInterface;
use Tchess\MessageManager;
use Tchess\Rule\InCheckRules;
use Tchess\Message\Message;

class KingRules implements EventSubscriberInterface, CheckingMoveInterface
{

    private $messageManager;
    private $inCheckRules;

    public function __construct(MessageManager $messageManager, InCheckRules $inCheckRules)
    {
        $this->messageManager = $messageManager;
        $this->inCheckRules = $inCheckRules;
    }

    public function onMoveChecking(MoveEvent $event)
    {
        $valid = $this->checkMove($event);
        if (is_bool($valid)) {
          $event->setValidMove($valid);
        }
    }

    public function checkMove(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();

        // @todo - Do we need reference sign here?
        $piece = &$board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        if (!$piece instanceof King) {
            return;
        }

        if (abs($move->getNewRow() - $move->getCurrentRow()) > 1) {
            return false;
        }

        if (abs($move->getNewColumn() - $move->getCurrentColumn()) > 1) {

            if ($piece->isMoved()) {
                return false;
            }

            if (abs($move->getNewColumn() - $move->getCurrentColumn()) != 2 || $move->getCurrentRow() != $move->getNewRow()) {
                return false;
            }

            if ($this->inCheckRules->isInCheck($event)) {
                // The king must not be in check while castling.
                return false;
            }

            // Do castling logic here.
            if ($move->getNewColumn() - $move->getCurrentColumn() == 2) {
                // Castle kingside.
                $rook = $board->getPiece($move->getNewRow(), $move->getCurrentColumn() + 3);
                if (!$rook instanceof Rook || $rook->isMoved()) {
                    return false;
                }

                if ($board->getPiece($move->getNewRow(), $move->getCurrentColumn() + 1) != null || $board->getPiece($move->getNewRow(), $move->getCurrentColumn() + 2) != null) {
                    return false;
                }
            } else if ($move->getNewColumn() - $move->getCurrentColumn() == -2) {
                // Queenside.
                $rook = $board->getPiece($move->getNewRow(), $move->getCurrentColumn() - 4);
                if (!$rook instanceof Rook || $rook->isMoved()) {
                    return false;
                }

                // There are 3 squares between the king and the queenside rook.
                if ($board->getPiece($move->getNewRow(), $move->getCurrentColumn() - 1) != null || $board->getPiece($move->getNewRow(), $move->getCurrentColumn() - 2) != null || $board->getPiece($move->getNewRow(), $move->getCurrentColumn() - 3) != null) {
                    return false;
                }
            }

            // Just a flag indicate that this king will do a catling on the
            // actually move.
            $piece->setCastled(true);
        }

        return true;
    }

    public function onMoveDoCastling(MoveEvent $event)
    {
        $room = $event->getRoom();
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
                $rook = $board->getPiece($move->getNewRow(), $move->getNewColumn() + 1);
                if (!$rook instanceof Rook || $rook->isMoved()) {
                    return;
                }

                // Kingside.
                $rook_move = new Move();
                $rook_move->setCurrentRow($move->getNewRow());
                $rook_move->setCurrentColumn($move->getNewColumn() + 1);
                $rook_move->setNewRow($move->getNewRow());
                $rook_move->setNewColumn($move->getNewColumn() - 1);
                $rook_move->setCastling(true);

                // Remove castling availability.
                $board->removeCastlingAvailability($color == 'white' ? 'K' : 'k');
            } else {
                $rook = $board->getPiece($move->getNewRow(), $move->getNewColumn() - 2);
                if (!$rook instanceof Rook || $rook->isMoved()) {
                    return;
                }

                // Queenside.
                $rook_move = new Move();
                $rook_move->setCurrentRow($move->getNewRow());
                $rook_move->setCurrentColumn($move->getNewColumn() - 2);
                $rook_move->setNewRow($move->getNewRow());
                $rook_move->setNewColumn($move->getNewColumn() + 1);
                $rook_move->setCastling(true);

                // Remove castling availability.
                $board->removeCastlingAvailability($color == 'white' ? 'Q' : 'q');
            }
            $rook_move->setColor($color);

            $board->movePiece($rook_move);
            $piece->setCastled(false);

            $message = new Message($room->getId(), 'move', array(
                'source' => $rook_move->getSource(),
                'target' => $rook_move->getTarget(),
                'color' => $rook_move->getColor(),
                'castling' => $rook_move->getCastling(),
            ));
            $this->messageManager->addMessage($message);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECK_MOVE => array(array('onMoveChecking', 0)),
            MoveEvents::MOVE => array(array('onMoveDoCastling', 0)),
        );
    }

}
