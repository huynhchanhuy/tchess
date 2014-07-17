<?php

namespace Tchess\Entity\Piece;

class Pawn extends Piece
{
    public function __toString()
    {
        return $this->color == 'white' ? 'P' : 'p';
    }
}
