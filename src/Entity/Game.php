<?php

namespace Tchess\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Tchess\EntityRepository\GameRepository")
 * @ORM\Table(name="game")
 */
class Game
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Room", inversedBy="game")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     */
    protected $room;

    /**
     * @ORM\Column(type="text")
     */
    protected $state;

    /**
     * @ORM\Column(type="string")
     */
    protected $currentTurn;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Game
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set currentTurn
     *
     * @param string $currentTurn
     * @return Game
     */
    public function setCurrentTurn($currentTurn)
    {
        $this->currentTurn = $currentTurn;

        return $this;
    }

    /**
     * Get currentTurn
     *
     * @return string
     */
    public function getCurrentTurn()
    {
        return $this->currentTurn;
    }

    /**
     * Set room
     *
     * @param \Tchess\Entity\Room $room
     * @return Game
     */
    public function setRoom(\Tchess\Entity\Room $room = null)
    {
        $this->room = $room;

        return $this;
    }

    /**
     * Get room
     *
     * @return \Tchess\Entity\Room
     */
    public function getRoom()
    {
        return $this->room;
    }
}
