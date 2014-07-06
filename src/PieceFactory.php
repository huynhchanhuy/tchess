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
        if ($serialized == '_') {
            return null;
        }
        else {
            $color = $serialized[0] == 'w' ? 'white' : 'black';
            switch ($serialized[1]) {
                case 'b':
                    $piece = new Bishop($color);
                    break;

                case 'k':
                    $piece = new King($color);
                    break;

                case 'n':
                    $piece = new Knight($color);
                    break;

                case 'p':
                    $piece = new Pawn($color);
                    break;

                case 'q':
                    $piece = new Queen($color);
                    break;

                case 'r':
                    $piece = new Rook($color);
                    break;

                default:
                    $piece = null;
                    break;
            }
        }
        return $piece;
    }
}
