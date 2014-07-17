<?php

namespace Tchess\Entity\Piece;

class Piece
{
    protected $color;
    protected $hasMoved;

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    public function __construct($color)
    {
        $this->color = $color;
        $this->hasMoved = false;
    }

    public function __toString()
    {
        return '';
    }

    public function isMoved()
    {
        return $this->hasMoved;
    }

    public function setHasMoved($hasMoved)
    {
        $this->hasMoved = $hasMoved;
    }
}
