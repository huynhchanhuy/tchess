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
     * @ORM\Column(type="string")
     */
    protected $turn;

    /**
     * @ORM\Column(type="text")
     *
     * Serialization of board.
     */
    protected $state;

    /**
     * Tchess\Entity\Board
     */
    protected $board;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $started;

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
     * Set turn
     *
     * @param string $turn
     * @return Game
     */
    public function setTurn($turn)
    {
        $this->turn = $turn;

        return $this;
    }

    /**
     * Get currentTurn
     *
     * @return string
     */
    public function getTurn()
    {
        return $this->turn;
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

    /**
     * Set board
     *
     * @param \Tchess\Entity\Board $board
     * @return Game
     */
    public function setBoard(Board $board = null)
    {
        $this->board = $board;

        return $this;
    }

    /**
     * Get board
     *
     * @return \Tchess\Entity\Board
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * Set started
     *
     * @param boolean $started
     * @return Game
     */
    public function setStarted($started)
    {
        $this->started = $started;

        return $this;
    }

    /**
     * Get started
     *
     * @return boolean
     */
    public function getStarted()
    {
        return $this->started;
    }

    /**
     * Save state of the board.
     *
     * @return Game
     */
    public function saveGame($serializer)
    {
        $boardString = $serializer->serialize($this->board, 'board_string');
        $this->state = $boardString;

        return $this;
    }

    /**
     * Load state of the board.
     *
     * @return Game
     */
    public function loadGame($serializer)
    {
        $this->board = $serializer->deserialize($this->state, 'Tchess\Entity\Board', 'board_string');

        return $this;
    }

}
