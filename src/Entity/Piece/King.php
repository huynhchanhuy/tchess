<?php

namespace Tchess\Entity\Piece;

class King extends Piece
{
    protected $hasMoved;
    protected $castled;

    public function __construct($color){
        parent::__construct($color);
        $this->hasMoved = false;
        $this->castled = false;
    }

    public function isMoved()
    {
        return $this->hasMoved;
    }

    public function setHasMoved($hasMoved)
    {
        $this->hasMoved = $hasMoved;
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
        return parent::__toString() . 'k';
    }
}
