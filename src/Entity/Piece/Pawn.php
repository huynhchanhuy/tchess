<?php

namespace Tchess\Entity\Piece;

class Pawn extends Piece
{
    protected $epAble;

    public function __construct($color){
        parent::__construct($color);
        $this->epAble = false;
    }

    public function __toString()
    {
        return $this->color == 'white' ? 'P' : 'p';
    }

    public function isEpAble()
    {
        return $this->epAble;
    }

    public function setEpAble($epAble)
    {
        $this->epAble = $epAble;
    }
}
