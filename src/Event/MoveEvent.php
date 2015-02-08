<?php

namespace Tchess\Event;

use Tchess\Entity\Room;
use Symfony\Component\EventDispatcher\Event;
use Tchess\Entity\Piece\Move;

class MoveEvent extends Event
{

    private $room;
    private $move;
    private $message;

    public function __construct(Room $room, $move)
    {
        $this->setRoom($room);
        $this->setMove($move);
    }

    public function getRoom()
    {
        return $this->room;
    }

    public function setRoom(Room $room)
    {
        $this->room = $room;
    }

    public function getMove()
    {
        return $this->move;
    }

    public function setMove(Move $move)
    {
        $this->move = $move;
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
