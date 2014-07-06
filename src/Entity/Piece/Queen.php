<?php

namespace Tchess\Entity\Piece;

class Queen extends Piece
{
    public function __toString()
    {
        return (string) parent . 'q';
    }
}
