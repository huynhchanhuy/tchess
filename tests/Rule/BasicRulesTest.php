<?php

namespace Tchess\Tests\Rule;

use Tchess\Tests\UnitTestBase;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Board;

class BasicRulesTest extends UnitTestBase
{
    public function testMoveToTheSameSquare()
    {
        $board = new Board();
        $board->initialize();
        $move = new Move($board, 'white', 'a1 a1');

        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move piece to the same square');
    }

    public function testMoveEmptySquare()
    {
        $board = new Board();
        $board->initialize();
        $move = new Move($board, 'white', 'a3 a4');

        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'There are no piece at a3');
    }

    public function testMoveOpponentPiece()
    {
        $board = new Board();
        $board->initialize();
        $move = new Move($board, 'white', 'a7 a6');

        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move opponent piece');
    }

    public function testTakeOwnPiece()
    {
        $board = new Board();
        $board->initialize();
        $move = new Move($board, 'white', 'a1 a2');

        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not take your own piece');
    }

}
