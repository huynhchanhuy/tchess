<?php

namespace Tchess\Entity\Piece;

class Knight extends Piece
{
    public function __toString()
    {
        return (string) parent . 'n';
    }
}
