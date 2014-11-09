<?php

namespace Tchess;

use Tchess\Entity\Piece\Bishop;
use Tchess\Entity\Piece\King;
use Tchess\Entity\Piece\Knight;
use Tchess\Entity\Piece\Pawn;
use Tchess\Entity\Piece\Queen;
use Tchess\Entity\Piece\Rook;
use Tchess\Entity\Piece\Move;

class PieceFactory
{
    public static function create($serialized, $x, $y, $ep)
    {
        if (empty($serialized)) {
            return null;
        }

        $color = ctype_upper($serialized) ? 'white' : 'black';
        switch (strtoupper($serialized)) {
            case 'B':
                $piece = new Bishop($color);
                if ($color == 'white') {
                    $notMoved = $x == 0 && ($y == 2 || $y == 5);
                }
                else {
                    $notMoved = $x == 7 && ($y == 2 || $y == 5);
                }
                break;

            case 'K':
                $piece = new King($color);
                if ($color == 'white') {
                    $notMoved = $x == 0 && $y == 4;
                }
                else {
                    $notMoved = $x == 7 && $y == 4;
                }
                break;

            case 'N':
                $piece = new Knight($color);
                if ($color == 'white') {
                    $notMoved = $x == 0 && ($y == 1 || $y == 6);
                }
                else {
                    $notMoved = $x == 7 && ($y == 1 || $y == 6);
                }
                break;

            case 'P':
                $piece = new Pawn($color);
                if ($color == 'white') {
                    $notMoved = $x == 1;
                }
                else {
                    $notMoved = $x == 6;
                }

                if (preg_match('/^[a-h]{1}(3|6){1}$/', $ep)) {
                    $move = new Move();
                    $move->setSource($ep);
                    $currentRow = $move->getCurrentRow() == 5 ? 4 : 3;
                    if ($currentRow == $x && $move->getCurrentColumn() == $y) {
                        $piece->setEpAble(true);
                    }
                }

                break;

            case 'Q':
                $piece = new Queen($color);
                if ($color == 'white') {
                    $notMoved = $x == 0 && $y == 3;
                }
                else {
                    $notMoved = $x == 7 && $y == 3;
                }
                break;

            case 'R':
                $piece = new Rook($color);
                if ($color == 'white') {
                    $notMoved = $x == 0 && ($y == 0 || $y == 7);
                }
                else {
                    $notMoved = $x == 7 && ($y == 0 || $y == 7);
                }
                break;

            default:
                $piece = null;
                break;
        }
        $piece->setHasMoved(!$notMoved);
        return $piece;
    }
}
