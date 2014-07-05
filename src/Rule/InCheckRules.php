<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Board;
use Tchess\Entity\Piece\King;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Piece\Piece;

class InCheckRules implements EventSubscriberInterface
{

    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function onMoveChecking(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();
        $color = $event->getColor();

        // Still valid after all rules.
        if ($event->isValidMove()) {
            $new_board = clone $board;
            $new_board->movePiece($move);

            // We wont dispatch MoveEvents::MOVE event, since it is not
            // neccessary in checking King is in check.

            if ($this->isInCheck($new_board, $color)) {
                // The king is still in check.
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
            MoveEvents::CHECH_MOVE => array(array('onMoveChecking', -1)),
        );
    }

    private function isInCheck(Board $board, $color)
    {
        $kingPos = $this->getKingPos($board, $color);
        list($row, $col) = $kingPos;

        for ($x = 0; $x < 8; $x++) {
            for ($y = 0; $y< 8; $y++) {
                $piece = $board->getPiece($x, $x);
                if ($piece != null && $piece instanceof Piece && $piece->getColor() != $color) {
                    $move = new Move();
                    $move->setCurrentRow($x);
                    $move->setCurrentColumn($y);
                    $move->setNewRow($row);
                    $move->setNewColumn($col);

                    $event = new MoveEvent($board, $move, $piece->getColor());
                    $this->dispatcher->dispatch(MoveEvents::CHECH_MOVE, $event);
                    if ($event->isValidMove()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function getKingPos(Board $board, $color){
        $row = 0;
        $col = 0;

        for ($x = 0; $x < 8; $x++) {
            for ($y = 0; $y < 8; $y++) {
                if($board->getPiece($x, $x) != null){
                    if($board->getPiece($x, $x) instanceof King && $board->getPiece($x, $x)->getColor() == $color){
                        $row = $x;
                        $col = $y;
                        break 2;
                    }
                }
            }
        }

        return array($row, $col);
    }

}
