<?php

namespace Tchess\Entity\Piece;

class Piece
{
    protected $color;

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
    }

    public function __toString()
    {
        return '';
    }
}
