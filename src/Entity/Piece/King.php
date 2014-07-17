<?php

namespace Tchess\Entity\Piece;

class King extends Piece
{
    protected $castled;

    public function __construct($color){
        parent::__construct($color);
        $this->castled = false;
    }

    public function isCastled()
    {
        return $this->castled;
    }

    public function setCastled($castled)
    {
        $this->castled = $castled;
    }

    public function __toString()
    {
        return $this->color == 'white' ? 'K' : 'k';
    }
}
