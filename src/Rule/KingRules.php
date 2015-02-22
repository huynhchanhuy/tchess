<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\King;
use Tchess\Entity\Piece\Rook;
use Tchess\Entity\Piece\Move;
use Tchess\Rule\MoveCheckerInterface;
use Tchess\MessageManager;
use Tchess\Rule\InCheckRules;
use Tchess\Message\Message;

class KingRules implements EventSubscriberInterface, MoveCheckerInterface
{

    private $messageManager;
    private $inCheckRules;

    public function __construct(MessageManager $messageManager, InCheckRules $inCheckRules)
    {
        $this->messageManager = $messageManager;
        $this->inCheckRules = $inCheckRules;
    }

    public function checkMove(Move $move)
    {
        $board = $move->getBoard();
        $color = $move->getColor();
        list($currentRow, $currentColumn) = Move::getIndex($move->getSource());
        list($newRow, $newColumn) = Move::getIndex($move->getTarget());

        $piece = $board->getPiece($currentRow, $currentColumn);
        if (!$piece instanceof King) {
            return;
        }

        if (abs($newRow - $currentRow) > 1) {
            return 'Can not move more than one row';
        }

        if (abs($newColumn - $currentColumn) > 1) {

            if ($piece->isMoved()) {
                return 'Can not do castling if the king is moved';
            }

            if (abs($newColumn - $currentColumn) != 2 || $currentRow != $newRow) {
                return 'Invalid castling move';
            }

            if ($this->inCheckRules->isInCheck($board, $color)) {
                // The king must not be in check while castling.
                return 'The king is in check, can not do castling';
            }

            // The king does not end up in check.
            // @see InCheckRules::checkMove().

            // Do castling logic here.
            if ($newColumn - $currentColumn == 2) {
                // Castle kingside.
                $rook = $board->getPiece($newRow, $currentColumn + 3);
                if (!$rook instanceof Rook || $rook->isMoved()) {
                    return 'Kingside rook is moved';
                }

                if ($board->getPiece($newRow, $currentColumn + 1) != null || $board->getPiece($newRow, $currentColumn + 2) != null) {
                    return 'There are pieces between the king and kingside rook';
                }
            } else if ($newColumn - $currentColumn == -2) {
                // Queenside.
                $rook = $board->getPiece($newRow, $currentColumn - 4);
                if (!$rook instanceof Rook || $rook->isMoved()) {
                    return 'Queenside rook is moved';
                }

                // There are 3 squares between the king and the queenside rook.
                if ($board->getPiece($newRow, $currentColumn - 1) != null || $board->getPiece($newRow, $currentColumn - 2) != null || $board->getPiece($newRow, $currentColumn - 3) != null) {
                    return 'There are pieces between the king and queenside rook';
                }
            }

            // Just a flag indicate that this king will do a catling on the
            // actually move.
            $piece->setCastled(true);
        }
    }

    public function onMoveDoCastling(MoveEvent $event)
    {
        $room = $event->getRoom();
        $move = $event->getMove();
        $board = $move->getBoard();
        $color = $move->getColor();
        list($currentRow, $currentColumn) = Move::getIndex($move->getSource());
        list($newRow, $newColumn) = Move::getIndex($move->getTarget());
        $piece = $board->getPiece($newRow, $newColumn);

        if (!$piece instanceof King) {
            return;
        }

        if ($piece->isCastled()) {
            // Move rook.
            if ($newColumn - $currentColumn == 2) {
                $rook = $board->getPiece($newRow, $newColumn + 1);
                if (!$rook instanceof Rook || $rook->isMoved()) {
                    return;
                }

                // Kingside.
                $source = Move::getSquare($newRow, $newColumn + 1);
                $target = Move::getSquare($newRow, $newColumn - 1);
            } else {
                $rook = $board->getPiece($newRow, $newColumn - 2);
                if (!$rook instanceof Rook || $rook->isMoved()) {
                    return;
                }

                // Queenside.
                $source = Move::getSquare($newRow, $newColumn - 2);
                $target = Move::getSquare($newRow, $newColumn + 1);
            }

            $board->movePiece($source, $target);
            $piece->setCastled(false);

            $message = new Message($room->getId(), 'move', array(
                'source' => $source,
                'target' => $target,
                'color' => $color,
                'castling' => true,
            ));
            $this->messageManager->addMessage($message);
        }
    }

    public function onMoveRemoveCastlingAvailability(MoveEvent $event)
    {
        $move = $event->getMove();
        $board = $move->getBoard();
        $color = $move->getColor();
        list($newRow, $newColumn) = Move::getIndex($move->getTarget());
        $piece = $board->getPiece($newRow, $newColumn);

        if (!$piece instanceof King) {
            return;
        }

        // Remove castling availability.
        $board->removeCastlingAvailability($color == 'white' ? 'K' : 'k');
        $board->removeCastlingAvailability($color == 'white' ? 'Q' : 'q');
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::MOVE => array(
                array('onMoveDoCastling', 0),
                array('onMoveRemoveCastlingAvailability', 0),
            ),
        );
    }

    public static function getRules()
    {
        return array(array('checkMove', 0));
    }

}
