<?php

namespace Tchess\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Tchess\EntityRepository\PlayerRepository")
 * @ORM\Table(name="player")
 */
class Player
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Room", inversedBy="players")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $room;

    /**
     * @ORM\Column(type="string")
     */
    protected $sid;

    /**
     * @ORM\Column(type="string")
     */
    protected $color;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

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
     * Set sid
     *
     * @param string $sid
     * @return Player
     */
    public function setSid($sid)
    {
        $this->sid = $sid;

        return $this;
    }

    /**
     * Get sid
     *
     * @return string
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set color
     *
     * @param string $color
     * @return Player
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set room
     *
     * @param \Tchess\Entity\Room $room
     * @return Player
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
     * Set name
     *
     * @param string $name
     * @return Player
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}
