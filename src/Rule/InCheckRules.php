<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Tchess\Entity\Board;
use Tchess\Entity\Piece\King;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Piece\Piece;
use Tchess\Rule\MoveCheckerInterface;

class InCheckRules implements EventSubscriberInterface, MoveCheckerInterface
{

    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function checkMove(Move $move)
    {
        $board = $move->getBoard();
        $color = $move->getColor();

        $newBoard = clone $board;
        $newBoard->movePiece($move->getSource(), $move->getTarget());

        // We wont dispatch MoveEvents::MOVE event, because we are checking King
        // is in check, not really move the piece.

        if ($this->isInCheck($newBoard, $color)) {
            // The king is still in check.
            return 'The king end up in check';
        }
    }

    public function isInCheck(Board $board, $color)
    {
        $kingPos = $this->getKingPos($board, $color);
        list($row, $col) = $kingPos;
        $target = Move::getSquare($row, $col);

        for ($x = 0; $x < 8; $x++) {
            for ($y = 0; $y < 8; $y++) {
                $piece = $board->getPiece($x, $y);
                if ($piece instanceof Piece && $piece->getColor() != $color) {
                    $source = Move::getSquare($x, $y);
                    $move = new Move($board, $piece->getColor(), "$source $target");

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

    public static function getSubscribedEvents()
    {
        return array();
    }

}
