<?php

namespace Tchess\Event;

use Tchess\Entity\Player;
use Tchess\Entity\Room;
use Symfony\Component\EventDispatcher\Event;

class RoomEvent extends Event
{

    private $player;
    private $room;

    public function __construct(Room $room, Player $player)
    {
        $this->setPlayer($player);
        $this->setRoom($room);
    }

    public function getPlayer()
    {
        return $this->player;
    }

    public function setPlayer($player)
    {
        $this->player = $player;
    }

    public function getRoom()
    {
        return $this->room;
    }

    public function setRoom($room)
    {
        $this->room = $room;
    }

}
