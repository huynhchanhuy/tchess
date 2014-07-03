<?php

namespace Tchess\Entity;

class Board
{

    protected $pieces;

    public function __construct()
    {
        $this->initialize();
    }

    public function initialize()
    {
        for($x = 0; $x < 8; $x++) {
            for($y = 0; $y < 8; $y++) {
                $this->pieces[$y][$x] = null;
            }
        }

        // White pawns
        for($x = 0; $x < 8; $x++) {
            $this->pieces[1][$x] = new Pawn("white");
        }

        // Black pawns
        for($x = 0; $x < 8; $x++){
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
     * Parses the user's string input for a move
     * @param string move
     * @return An int array of size 4 with the initial x, y positions and the final x, y positions in that order
     */
    public function parseInput($move) {
        $returnArray = array();

        $split = explode(' ', $move);
        $returnArray[1] = $this->charToInt(strtolower($split[0][0])); // Initial x.
        $returnArray[0] = (int) ($split[0][1]) - 1; // Initial y.

        $returnArray[3] = $this->charToInt(strtolower($split[1][0])); // Final x.
        $returnArray[2] = (int) ($split[1][1]) - 1; // Final y.
        return $returnArray;

    }

    /**
     * Returns an integer corresponding to the user input
     */
    public function charToInt($ch) {
        switch($ch) {
            case 'a': return 0;
            case 'b': return 1;
            case 'c': return 2;
            case 'd': return 3;
            case 'e': return 4;
            case 'f': return 5;
            case 'g': return 6;
            case 'h': return 7;
            default:
                throw new InvalidArgumentException('Invalid column letter. It must be one of these letter (a, b, c, d, e, f, g ,h).');
                break;
        }
    }

}
