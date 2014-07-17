<?php

namespace Tchess\Entity\Piece;

class Rook extends Piece
{
    public function __toString()
    {
        return $this->color == 'white' ? 'R' : 'r';
    }
}
