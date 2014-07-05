<?php

namespace Tchess\Entity\Piece;

class Pawn extends Piece
{
    protected $hasMoved;

    public function __construct($color){
        parent::__construct($color);
        $this->hasMoved = false;
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