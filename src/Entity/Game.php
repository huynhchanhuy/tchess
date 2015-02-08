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
     *
     * Serialization of board.
     */
    protected $state;

    /**
     * @ORM\Column(type="string", nullable=true, options={"default":""})
     */
    protected $highlights;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't setup any things here, because this clases is used in many
        // places.
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
     * Add highlight square
     *
     * @param string $square
     * @return Game
     */
    public function addHighlight($source, $target, $color)
    {
        $items = explode(' ', $this->highlights);
        while (count($items) > 3) {
            array_shift($items);
        }
        array_push($items, $source, $target, $color);
        $this->highlights = implode(' ', $items);

        return $this;
    }

    /**
     * Set highlights
     *
     * @param string $highlights
     * @return Game
     */
    public function setHighlights($highlights)
    {
        $this->highlights = $highlights;

        return $this;
    }

    /**
     * Get highlights
     *
     * @return string
     */
    public function getHighlights()
    {
        return $this->highlights;
    }

}
