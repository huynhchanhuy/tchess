<?php

namespace Tchess\Event;

use Symfony\Component\EventDispatcher\Event;
use Tchess\Entity\Piece\Move;

class MoveEvent extends Event
{

    private $roomId;
    private $move;

    public function __construct($roomId, $move)
    {
        $this->setRoomId($roomId);
        $this->setMove($move);
    }

    public function getRoomId()
    {
        return $this->roomId;
    }

    public function setRoomId($roomId)
    {
        $this->roomId = $roomId;
    }

    public function getMove()
    {
        return $this->move;
    }

    public function setMove(Move $move)
    {
        $this->move = $move;
    }

}
