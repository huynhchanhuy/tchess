<?php

namespace Tchess;

use Tchess\Entity\Piece\Bishop;
use Tchess\Entity\Piece\King;
use Tchess\Entity\Piece\Knight;
use Tchess\Entity\Piece\Pawn;
use Tchess\Entity\Piece\Queen;
use Tchess\Entity\Piece\Rook;

class PieceFactory
{
    public static function create($serialized)
    {
        if (empty($serialized)) {
            return null;
        }

        $color = ctype_upper($serialized) ? 'white' : 'black';
        switch (strtoupper($serialized)) {
            case 'B':
                $piece = new Bishop($color);
                break;

            case 'K':
                $piece = new King($color);
                break;

            case 'N':
                $piece = new Knight($color);
                break;

            case 'P':
                $piece = new Pawn($color);
                break;

            case 'Q':
                $piece = new Queen($color);
                break;

            case 'R':
                $piece = new Rook($color);
                break;

            default:
                $piece = null;
                break;
        }
        return $piece;
    }
}
