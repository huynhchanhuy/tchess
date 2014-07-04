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

    public function __construct($move)
    {
        $this->move = $move;
        $parts = explode(' ', $move);
        if (count($parts) != 2) {
            throw new \InvalidArgumentException('Move must be 2 parts connected by a space e.g. "a1 b2".');
        }
        list($this->source, $this->target) = $parts;
        $this->currentRow = $this->getRow($this->source[1]);
        $this->currentColumn = $this->getColumn($this->source[0]);
        $this->newRow = $this->getRow($this->target[1]);
        $this->newColumn = $this->getColumn($this->target[0]);
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
}
