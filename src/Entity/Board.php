<?php

namespace Tchess\Entity;

use Tchess\Entity\Piece\Move;
use Tchess\Entity\Piece\King;
use Tchess\Entity\Piece\Rook;
use Tchess\Entity\Piece\Pawn;
use Tchess\Entity\Piece\Piece;
use Tchess\Entity\Piece\Knight;
use Tchess\Entity\Piece\Bishop;
use Tchess\Entity\Piece\Queen;
use Tchess\PieceFactory;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class Board implements NormalizableInterface, DenormalizableInterface
{

    protected $pieces;

    protected $activeColor;

    protected $castlingAvailability;

    protected $enPassantTarget;

    protected $fullmoveNumber;

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

    /**
     * Set active color
     *
     * @param string $activeColor
     */
    public function setActiveColor($activeColor)
    {
        $this->activeColor = $activeColor;
    }

    /**
     * Get active color
     *
     * @return string
     */
    public function getActiveColor()
    {
        return $this->activeColor;
    }

    /**
     * Set castling availability
     *
     * @param string $castlingAvailability
     */
    public function setCastlingAvailability($castlingAvailability)
    {
        $this->castlingAvailability = $castlingAvailability;
    }

    /**
     * Get castling availability
     *
     * @return string
     */
    public function getCastlingAvailability()
    {
        return $this->castlingAvailability;
    }

    /**
     * Remove castling availability
     *
     * @param string $availability
     */
    public function removeCastlingAvailability($availability)
    {
        $this->castlingAvailability = str_replace($availability, '', $this->castlingAvailability);
    }

    /**
     * Set en passant target
     *
     * @param string $target
     */
    public function setEnPassantTarget($target)
    {
        $this->enPassantTarget = $target;
    }

    /**
     * Get en passant target
     *
     * @return string
     */
    public function getEnPassantTarget()
    {
        return $this->enPassantTarget;
    }

    /**
     * Set fullmove number
     *
     * @param int $fullmoveNumber
     */
    public function setFullmoveNumber($fullmoveNumber)
    {
        $this->fullmoveNumber = $fullmoveNumber;
    }

    /**
     * Get fullmove number
     *
     * @return int
     */
    public function getFullmoveNumber()
    {
        return $this->fullmoveNumber;
    }

    /**
     * Increase fullmove number
     *
     * @return int
     */
    public function increaseFullmoveNumber()
    {
        $this->fullmoveNumber++;
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
            $this->pieces[6][$x] = new Pawn("black");
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

    public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null, array $context = array())
    {
        for ($x = 0; $x < 8; $x++) {
            for ($y = 0; $y < 8; $y++) {
                $this->pieces[$x][$y] = (isset($data[$x][$y]) && !empty($data[$x][$y])) ? PieceFactory::create($data[$x][$y]) : null;
            }
        }
    }

    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = array())
    {
        $state = array();
        for ($x = 0; $x < 8; $x++) {
            for ($y = 0; $y < 8; $y++) {
                $state[$x][$y] = ($this->pieces[$x][$y] != null) ? (string) $this->pieces[$x][$y] : '';
            }
        }
        return $state;
    }

}
