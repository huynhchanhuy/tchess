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
     * Remove piece.
     */
    public function removePiece($row, $column)
    {
        $this->pieces[$row][$column] = null;
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
        for ($x = 2; $x < 6; $x++) {
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

        // Other attributes.
        $this->setActiveColor('white');
        $this->setCastlingAvailability('KQkq');
        $this->setEnPassantTarget('');
        $this->setFullmoveNumber(1);
    }

    /**
     * Move a piece.
     *
     * @return \Tchess\Entity\Piece|null
     */
    public function movePiece($source, $target)
    {
        list($currentRow, $currentColumn) = Move::getIndex($source);
        list($newRow, $newColumn) = Move::getIndex($target);

        //Switch the two spots on the board.
        $this->pieces[$newRow][$newColumn] = $this->pieces[$currentRow][$currentColumn];
        $this->pieces[$currentRow][$currentColumn] = null;

        $piece = &$this->pieces[$newRow][$newColumn];
        $piece->setHasMoved(true);
    }

    public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null, array $context = array())
    {
        for ($x = 0; $x < 8; $x++) {
            for ($y = 0; $y < 8; $y++) {
                $this->pieces[$x][$y] = (isset($data['pieces'][$x][$y]) && !empty($data['pieces'][$x][$y])) ? PieceFactory::create($data['pieces'][$x][$y], $x, $y, $data['ep']) : null;
            }
        }

        $this->activeColor = $data['active'] == 'w' ? 'white' : 'black';
        if (!empty($data['castling']) && (preg_match('/^K{0,1}Q{0,1}k{0,1}q{0,1}$/', $data['castling']) || $data['castling'] == '-')) {
            if ($data['castling'] == '-') {
                $this->castlingAvailability = '';
            }
            else {
                $this->castlingAvailability = $data['castling'];
            }
        }
        if (preg_match('/^[a-h]{1}(3|6){1}$/', $data['ep'])) {
            $this->enPassantTarget = $data['ep'];
        }
        else {
            $this->enPassantTarget = '';
        }
        $this->fullmoveNumber = $data['fullmove'] > 0 ? $data['fullmove'] : 1;
    }

    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = array())
    {
        $data = array();
        for ($x = 0; $x < 8; $x++) {
            for ($y = 0; $y < 8; $y++) {
                $data['pieces'][$x][$y] = ($this->pieces[$x][$y] != null) ? (string) $this->pieces[$x][$y] : '';
            }
        }

        $data['active'] = $this->activeColor == 'white' ? 'w' : 'b';
        if (preg_match('/^K{0,1}Q{0,1}k{0,1}q{0,1}$/', $this->castlingAvailability)) {
            if (empty($this->castlingAvailability)) {
                $data['castling'] = '-';
            }
            else {
                $data['castling'] = $this->castlingAvailability;
            }
        }
        if (preg_match('/^[a-h]{1}(3|6){1}$/', $this->enPassantTarget)) {
            $data['ep'] = $this->enPassantTarget;
        }
        else {
            $data['ep'] = '-';
        }
        $data['fullmove'] = $this->fullmoveNumber > 0 ? $this->fullmoveNumber : 1;

        return $data;
    }

}
