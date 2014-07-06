<?php

namespace Tchess\Entity\Piece;

class Bishop extends Piece
{
    public function __toString()
    {
        return (string) parent . 'b';
    }
}
