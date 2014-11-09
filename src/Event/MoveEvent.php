<?php

namespace Tchess\Event;

use Tchess\Entity\Room;
use Tchess\Entity\Board;
use Symfony\Component\EventDispatcher\Event;
use Tchess\Entity\Piece\Move;

class MoveEvent extends Event
{

    private $room;
    private $board;
    private $move;
    private $validMove;
    private $message;
    private $color;

    public function __construct(Room $room, Board $board, $move, $color)
    {
        $this->setRoom($room);
        $this->setBoard($board);
        $this->setMove($move);
        $this->setColor($color);
        $this->validMove = false;
    }

    public function getRoom()
    {
        return $this->room;
    }

    public function setRoom(Room $room)
    {
        $this->room = $room;
    }

    public function getBoard()
    {
        return $this->board;
    }

    public function setBoard(Board $board)
    {
        $this->board = $board;
    }

    public function getMove()
    {
        return $this->move;
    }

    public function setMove(Move $move)
    {
        $this->move = $move;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        $this->color = $color;
    }

    public function isValidMove()
    {
        return $this->validMove;
    }

    public function setValidMove($validMove)
    {
        $this->validMove = $validMove;

        if ($validMove == false) {
            // If any rule is broke, we stop checking.
            $this->stopPropagation();
        }
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

}
