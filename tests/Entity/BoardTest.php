<?php

namespace Tchess\Tests\Entity;

use Tchess\Tests\UnitTestBase;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Board;

class BoardTest extends UnitTestBase
{
    public function testCloneBoard()
    {
        $board = new Board();
        $board->initialize();
        $board2 = clone $board;
        $source = 'a2';
        $target = 'a3';

        $board2->movePiece($source, $target);

        list($currentRow, $currentColumn) = Move::getIndex($source);
        $piece = $board->getPiece($currentRow, $currentColumn);
        $this->assertFalse($piece->isMoved(), 'Piece in the original board is not moved');

        list($newRow, $newColumn) = Move::getIndex($target);
        $piece = $board->getPiece($newRow, $newColumn);
        $this->assertNull($piece, 'Target square in the original board is empty');
    }

}
