<?php

namespace Tchess\Entity;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Piece\King;
use Tchess\Entity\Piece\Rook;
use Tchess\Entity\Piece\Pawn;
use Tchess\Entity\Piece\Piece;

class Board
{

    protected $pieces;

    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Get piece.
     *
     * @return \Tchess\Entity\Piece|null
     */
    public function getPiece($row, $column)
    {
        return $this->pieces[$row][$column];
    }

    /**
     * Set piece.
     */
    public function setPiece(Piece $piece, $row, $column)
    {
        $this->pieces[$row][$column] = $piece;
    }

    public function initialize()
    {
        for ($x = 0; $x < 8; $x++) {
            for ($y = 0; $y < 8; $y++) {
                $this->pieces[$x][$y] = null;
            }
        }

        // White pawns
        for ($x = 0; $x < 8; $x++) {
            $this->pieces[1][$x] = new Pawn("white");
        }

        // Black pawns
        for ($x = 0; $x < 8; $x++) {
            $this->pieces[6][x] = new Pawn("black");
        }

        //Rooks
        $this->pieces[0][0] = new Rook("white");
        $this->pieces[0][7] = new Rook("white");
        $this->pieces[7][7] = new Rook("black");
        $this->pieces[7][0] = new Rook("black");

        //Knights
        $this->pieces[0][1] = new Knight("white");
        $this->pieces[0][6] = new Knight("white");
        $this->pieces[7][6] = new Knight("black");
        $this->pieces[7][1] = new Knight("black");

        //Bishops
        $this->pieces[0][2] = new Bishop("white");
        $this->pieces[0][5] = new Bishop("white");
        $this->pieces[7][2] = new Bishop("black");
        $this->pieces[7][5] = new Bishop("black");

        //Queens
        $this->pieces[0][3] = new Queen("white");
        $this->pieces[7][3] = new Queen("black");

        //Kings
        $this->pieces[0][4] = new King("white");
        $this->pieces[7][4] = new King("black");
    }

    /**
     * Move a piece.
     *
     * @return \Tchess\Entity\Piece|null
     */
    public function movePiece(Move $move)
    {
        //Switch the two spots on the board.
        $this->pieces[$move->getNewRow()][$move->getNewColumn()] = $this->pieces[$move->getCurrentRow()][$move->getCurrentColumn()];
        $this->pieces[$move->getCurrentRow()][$move->getCurrentColumn()] = null;

        $piece = &$this->pieces[$move->getNewRow()][$move->getNewColumn()];
        if ($piece instanceof King || $piece instanceof Rook || $piece instanceof Pawn) {
            $piece->setHasMoved(true);
        }
    }

}
