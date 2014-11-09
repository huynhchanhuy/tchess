<?php

namespace Tchess\Entity\Piece;

class Move
{
    protected $roomId;
    protected $color;
    protected $move;
    protected $source;
    protected $target;
    protected $currentRow;
    protected $currentColumn;
    protected $newRow;
    protected $newColumn;
    protected $promotion;
    protected $castling;

    public function __construct($color = 'white', $move = '')
    {
        $this->setColor($color);

        if (!empty($move)) {
            $this->move = $move;
            $parts = explode(' ', $move);
            if (count($parts) == 2) {
                list($source, $target) = $parts;
                $promotion = 'Q';
            }
            else if (count($parts) == 3) {
                list($source, $target, $promotion) = $parts;
            }
            else {
                throw new \InvalidArgumentException('Move must be "a1 b2" or "a1 b2 Q".');
            }
            $this->setSource($source);
            $this->setTarget($target);
            $this->setPromotion($promotion);
        }

        $this->castling = false;
    }

    public function getRoomId()
    {
        return $this->roomId;
    }

    public function setRoomId($roomId)
    {
        $this->roomId = $roomId;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getMove()
    {
        return $this->move;
    }

    /**
     * Get column.
     */
    private function getColumn($column, $char_to_number = true)
    {
        $map = array(
            'a' => 0,
            'b' => 1,
            'c' => 2,
            'd' => 3,
            'e' => 4,
            'f' => 5,
            'g' => 6,
            'h' => 7,
        );
        if (!$char_to_number) {
            $map = array_flip($map);
        }
        $ch = strtolower($column);
        if (isset($map[$ch])) {
            return $map[$ch];
        }
        throw new \InvalidArgumentException('Invalid board column. It must be one of these letter (a, b, c, d, e, f, g ,h).');
    }

    /**
     * Get row.
     */
    private function getRow($row, $number_to_index = true)
    {
        if ((($number_to_index) && ($row < 1 || $row > 8)) || (!($number_to_index) && ($row < 0 || $row > 7))) {
            throw new \InvalidArgumentException('Invalid board row');
        }
        if ($number_to_index) {
            return (int) ($row) - 1;
        }
        else {
            return (int) ($row) + 1;
        }
    }

    public function getCurrentRow()
    {
        return $this->currentRow;
    }

    public function setCurrentRow($currentRow)
    {
        $this->currentRow = $currentRow;
    }

    public function getCurrentColumn()
    {
        return $this->currentColumn;
    }

    public function setCurrentColumn($currentColumn)
    {
        $this->currentColumn = $currentColumn;
    }

    public function getNewRow()
    {
        return $this->newRow;
    }

    public function setNewRow($newRow)
    {
        $this->newRow = $newRow;
    }

    public function getNewColumn()
    {
        return $this->newColumn;
    }

    public function setNewColumn($newColumn)
    {
        $this->newColumn = $newColumn;
    }

    public function getSource()
    {
        if (!empty($this->source)) {
            return $this->source;
        }
        else {
            // Build source.
            $this->source = $this->getColumn($this->getCurrentColumn(), false) . $this->getRow($this->getCurrentRow(), false);
        }
        return $this->source;
    }

    public function setSource($source)
    {
        $this->setCurrentRow($this->getRow($source[1]));
        $this->setCurrentColumn($this->getColumn($source[0]));
        $this->source = $source;
    }

    public function getTarget()
    {
        if (!empty($this->target)) {
            return $this->target;
        }
        else {
            // Build source.
            $this->target = $this->getColumn($this->getNewColumn(), false) . $this->getRow($this->getNewRow(), false);
        }
        return $this->target;
    }

    public function setTarget($target)
    {
        $this->setNewRow($this->getRow($target[1]));
        $this->setNewColumn($this->getColumn($target[0]));
        $this->target = $target;
    }

    public function getPromotion()
    {
        return $this->promotion;
    }

    public function setPromotion($promotion)
    {
        $promotion = strtoupper($promotion);
        if (!in_array($promotion, array('Q', 'K', 'B', 'R'))) {
            $promotion = 'Q';
        }
        $this->promotion = $promotion;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        if (!in_array($color, array('white', 'black'))) {
            $color = 'white';
        }
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
