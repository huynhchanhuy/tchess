<?php

namespace Tchess;

use Tchess\Entity\Piece\Move;

class MoveManager
{

    /**
     * @var Move[]
     */
    protected $moves;

    /**
     * Add move.
     *
     * @param Move $move
     */
    public function addMove(Move $move)
    {
        $this->moves[] = $move;
    }

    /**
     * Get moves.
     *
     * @return Move[]
     */
    public function getMoves()
    {
        return $this->moves;
    }

}
