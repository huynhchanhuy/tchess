<?php

namespace Tchess\Entity\Piece;

use Symfony\Component\Validator\Constraints as Assert;
use Tchess\Entity\Board;

class Move
{
    /**
     * @Assert\Choice(callback = "getColors")
     */
    protected $color;

    /**
     * @Assert\Regex("/^[a-h][1-8]$/")
     */
    protected $source;

    /**
     * @Assert\Regex("/^[a-h][1-8]$/")
     */
    protected $target;

    /**
     * @Assert\Choice(callback = "getPromotions")
     */
    protected $promotion;

    /**
     * @Assert\Type(type="bool")
     */
    protected $castling;

    /**
     * Chess board.
     *
     * @var Tchess\Entity\Board
     */
    private $board;

    /**
     * Column map.
     *
     * @var array
     */
    protected static $map = array(
        'a' => 0,
        'b' => 1,
        'c' => 2,
        'd' => 3,
        'e' => 4,
        'f' => 5,
        'g' => 6,
        'h' => 7,
    );

    public function __construct(Board $board, $color = 'white', $move = '', $castling = false)
    {
        $this->setBoard($board);
        $this->setColor($color);

        if (!empty($move)) {
            $parts = explode(' ', $move);
            if (count($parts) == 2) {
                list($this->source, $this->target) = $parts;
                $this->promotion = 'Q';
            }
            else if (count($parts) == 3) {
                list($this->source, $this->target, $this->promotion) = $parts;
            }
            else {
                throw new \InvalidArgumentException('Move must be "a1 b2" or "a1 b2 Q".');
            }
        }

        $this->setCastling($castling);
    }

    public function getBoard()
    {
        return $this->board;
    }

    public function setBoard(Board $board)
    {
        $this->board = $board;
    }

    /**
     * Get valid colors.
     *
     * @return array
     */
    public static function getColors()
    {
        return array('white', 'black');
    }

    /**
     * Get valid promotions.
     *
     * @return array
     */
    public static function getPromotions()
    {
        return array('Q', 'K', 'B', 'R');
    }

    /**
     * Get index from square.
     *
     * @param string $square
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function getIndex($square = 'a1')
    {
        if (!preg_match("/^[a-h]{1}[1-8]{1}$/", $square)) {
            throw new \InvalidArgumentException('Invalid square');
        }
        $column = static::$map[$square[0]];
        $row = ((int) $square[1]) - 1;
        return array($row, $column);
    }

    /**
     * Get square from index.
     *
     * @param int $row
     * @param int $column
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function getSquare($row = 0, $column = 0)
    {
        if ($row < 0 || $row > 7 || $column < 0 || $column > 7) {
            throw new \InvalidArgumentException('Invalid index');
        }
        $map = array_flip(static::$map);
        return $map[$column] . ($row + 1);
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSource($source)
    {
        $this->source = $source;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setTarget($target)
    {
        $this->target = $target;
    }

    public function getPromotion()
    {
        return $this->promotion;
    }

    public function setPromotion($promotion)
    {
        $this->promotion = $promotion;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        $this->color = $color;
    }

    public function getCastling()
    {
        return $this->castling;
    }

    public function setCastling($castling)
    {
        $this->castling = $castling ? true : false;
    }
}
