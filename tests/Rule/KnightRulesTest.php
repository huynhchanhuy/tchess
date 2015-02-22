<?php

namespace Tchess\Tests\Rule;

use Tchess\Tests\UnitTestBase;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Board;

class KnightRulesTest extends UnitTestBase
{
    public function testInvalidMove()
    {
        $board = new Board();
        $board->initialize();

        $move = new Move($board, 'white', 'b1 b3');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move piece to the same square');

        $move = new Move($board, 'white', 'b1 d3');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move piece to the same square');
    }

    public function testValidMove()
    {
        $board = new Board();
        $board->initialize();

        $move = new Move($board, 'white', 'g1 f3');
        $errors = $this->validator->validate($move);
        $this->assertEquals(0, count($errors), 'Valid move');
    }

}
