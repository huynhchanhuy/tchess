<?php

namespace Tchess\Entity\Piece;

class Move
{
    protected $move;
    protected $source;
    protected $target;
    protected $currentRow;
    protected $currentColumn;
    protected $newRow;
    protected $newColumn;
    protected $promotion;

    public function __construct($move = '')
    {
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
    private function getColumn($column)
    {
        $ch = strtolower($column);
        switch ($ch) {
            case 'a': return 0;
            case 'b': return 1;
            case 'c': return 2;
            case 'd': return 3;
            case 'e': return 4;
            case 'f': return 5;
            case 'g': return 6;
            case 'h': return 7;
            default:
                throw new \InvalidArgumentException('Invalid board column. It must be one of these letter (a, b, c, d, e, f, g ,h).');
                break;
        }
    }

    /**
     * Get row.
     */
    private function getRow($row)
    {
        if ($row < 1 || $row > 8) {
            throw new \InvalidArgumentException('Invalid board row. It must be in range from 1 to 8.');
        }
        return (int) ($row) - 1;
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
        if (!in_array($promotion, array('Q', 'K', 'B', 'R'))) {
            $promotion = 'Q';
        }
        $this->promotion = $promotion;
    }
}
