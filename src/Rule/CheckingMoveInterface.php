<?php

namespace Tchess\Rule;

use Tchess\Entity\Board;
use Tchess\Entity\Piece\Move;

interface CheckingMoveInterface
{
    public function checkMove(Board $board, Move $move, $color = 'white');
}
