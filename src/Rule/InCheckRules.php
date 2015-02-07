<?php

namespace Tchess\Rule;

use Symfony\Component\Validator\ValidatorInterface;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Board;
use Tchess\Entity\Piece\King;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Piece\Piece;
use Tchess\Rule\MoveCheckerInterface;

class InCheckRules implements MoveCheckerInterface
{

    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function checkMove(MoveEvent $event)
    {
        $board = $event->getBoard();
        $move = $event->getMove();

        $newBoard = clone $board;
        $newBoard->movePiece($move);

        $newEvent = clone $event;
        $newEvent->setBoard($newBoard);

        // We wont dispatch MoveEvents::MOVE event, because we are checking King
        // is in check, not really move the piece.

        if ($this->isInCheck($newEvent)) {
            // The king is still in check.
            return false;
        }

        return true;
    }

    public function isInCheck(MoveEvent $event)
    {
        $board = $event->getBoard();
        $color = $event->getColor();

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

                    $newEvent = clone $event;
                    $newEvent->setMove($move);
                    $newEvent->setColor($piece->getColor());

                    $errors = $this->validator->validate($move);
                    if (count($errors) == 0) {
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

    public static function getRules()
    {
        return array(array('checkMove', -1));
    }

}
