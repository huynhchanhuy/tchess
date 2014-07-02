<?php

namespace Tchess\Event;

use Doctrine\ORM\EntityManagerInterface;
use Tchess\Entity\Player;
use Symfony\Component\EventDispatcher\Event;

class GameEvent extends Event
{
    private $player;
    private $em;

    public function __construct(Player $player, EntityManagerInterface $em)
    {
        $this->setPlayer($player);
        $this->setEntityManager($em);
    }

    public function getPlayer()
    {
        return $this->player;
    }

    public function setPlayer($player)
    {
        $this->player = $player;
    }

    public function getEntityManager()
    {
        return $this->em;
    }

    public function setEntityManager($em)
    {
        $this->em = $em;
    }
}
