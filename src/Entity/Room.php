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
     * @ORM\OneToMany(targetEntity="Player", mappedBy="room")
     */
    protected $players;

    /**
     * @ORM\OneToOne(targetEntity="Game")
     */
    protected $game;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->players = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Add players
     *
     * @param \Tchess\Entity\Player $players
     * @return Room
     */
    public function addPlayer(\Tchess\Entity\Player $players)
    {
        $this->players[] = $players;

        return $this;
    }

    /**
     * Remove players
     *
     * @param \Tchess\Entity\Player $players
     */
    public function removePlayer(\Tchess\Entity\Player $players)
    {
        $this->players->removeElement($players);
    }

    /**
     * Get players
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlayers()
    {
        return $this->players;
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
