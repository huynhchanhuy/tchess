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
use Tchess\Rule\CheckingMoveInterface;

class InCheckRules implements EventSubscriberInterface, CheckingMoveInterface
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

        $valid = $this->checkMove($board, $move, $color);
        $event->setValidMove($valid);
    }

    public function checkMove(Board $board, Move $move, $color = 'white')
    {
        $new_board = clone $board;
        $new_board->movePiece($move);

        // We wont dispatch MoveEvents::MOVE event, because we are checking King
        // is in check, not really move the piece.

        if ($this->isInCheck($new_board, $color)) {
            // The king is still in check.
            return false;
        }

        return true;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECK_MOVE => array(array('onMoveChecking', -1)),
        );
    }

    public function isInCheck(Board $board, $color)
    {
        $kingPos = $this->getKingPos($board, $color);
        list($row, $col) = $kingPos;

        for ($x = 0; $x < 8; $x++) {
            for ($y = 0; $y < 8; $y++) {
                $piece = $board->getPiece($x, $y);
                if ($piece instanceof Piece && $piece->getColor() != $color) {
                    $move = new Move();
                    $move->setColor($color);
                    $move->setCurrentRow($x);
                    $move->setCurrentColumn($y);
                    $move->setNewRow($row);
                    $move->setNewColumn($col);

                    $event = new MoveEvent($board, $move, $piece->getColor());
                    if ($this->dispatcher->dispatch(MoveEvents::CHECK_MOVE, $event)->isValidMove()) {
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
                $piece = $board->getPiece($x, $y);
                if($piece instanceof King && $piece->getColor() == $color){
                    $row = $x;
                    $col = $y;
                    break 2;
                }
            }
        }

        return array($row, $col);
    }

}
