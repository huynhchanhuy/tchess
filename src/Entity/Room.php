<?php

namespace Tchess\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Tchess\EntityRepository\RoomRepository")
 * @ORM\Table(name="room")
 */
class Room
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $white;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $black;

    /**
     * @ORM\OneToOne(targetEntity="Game")
     */
    protected $game;


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
     * Set white
     *
     * @param string $white
     * @return Room
     */
    public function setWhite($white)
    {
        $this->white = $white;

        return $this;
    }

    /**
     * Get white
     *
     * @return string
     */
    public function getWhite()
    {
        return $this->white;
    }

    /**
     * Set black
     *
     * @param string $black
     * @return Room
     */
    public function setBlack($black)
    {
        $this->black = $black;

        return $this;
    }

    /**
     * Get black
     *
     * @return string
     */
    public function getBlack()
    {
        return $this->black;
    }

    /**
     * Set game
     *
     * @param \Tchess\Entity\Game $game
     * @return Room
     */
    public function setGame(\Tchess\Entity\Game $game = null)
    {
        $this->game = $game;

        return $this;
    }

    /**
     * Get game
     *
     * @return \Tchess\Entity\Game
     */
    public function getGame()
    {
        return $this->game;
    }
}
