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
    public static function create($serialized, $x, $y, $castling, $ep)
    {
        if (empty($serialized)) {
            return null;
        }
        $moved = false;

        $color = ctype_upper($serialized) ? 'white' : 'black';
        switch (strtoupper($serialized)) {
            case 'B':
                $piece = new Bishop($color);
                // We don't care about moved value.
                break;

            case 'K':
                $piece = new King($color);
                // If both rooks were moved, moved value is not important.
                if ($color == 'white') {
                    $moved = ($x != 0) || ($y != 4) || (strpos($castling, 'K') === false && strpos($castling, 'Q') === false);
                }
                else {
                    $moved = ($x != 7) || ($y != 4) || (strpos($castling, 'k') === false && strpos($castling, 'q') === false);
                }
                break;

            case 'N':
                $piece = new Knight($color);
                // We don't care about moved value.
                break;

            case 'P':
                $piece = new Pawn($color);
                if ($color == 'white') {
                    $moved = $x != 1;
                }
                else {
                    $moved = $x != 6;
                }

                if (preg_match('/^[a-h]{1}(3|6){1}$/', $ep)) {
                    list($epRow, $epColumn) = Move::getIndex($ep);
                    $currentRow = $epRow == 5 ? 4 : 3;
                    $currentColumn = $epColumn;
                    if ($currentRow == $x && $currentColumn == $y) {
                        $piece->setEpAble(true);
                    }
                }

                break;

            case 'Q':
                $piece = new Queen($color);
                // We don't care about moved value.
                break;

            case 'R':
                $piece = new Rook($color);
                // If the king was moved, moved value is not important.
                if ($color == 'white') {
                    $moved = $x != 0 || (($y != 0 || strpos($castling, 'Q') === false) && ($y != 7 || strpos($castling, 'K') === false));
                }
                else {
                    $moved = $x != 7 || (($y != 0 || strpos($castling, 'q') === false) && ($y != 7 || strpos($castling, 'k') === false));
                }
                break;

            default:
                $piece = null;
                break;
        }
        $piece->setHasMoved($moved);
        return $piece;
    }
}
