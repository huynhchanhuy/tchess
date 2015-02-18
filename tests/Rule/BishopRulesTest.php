<?php

namespace Tchess\Tests\Rule;

use Tchess\Tests\UnitTestBase;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Board;

class BishopRulesTest extends UnitTestBase
{
    public function testNotMoveDiagonally()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/pppppp1p/6p1/8/3P4/8/PPP1PPPP/RNBQKBNR w KQkq - 0 2', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'c1 f3');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Did not move diagonally');

        $move = new Move($board, 'white', 'e1 d3');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move like a knight');
    }

    public function testGoThroughAnotherPiece()
    {
        $board = new Board();
        $board->initialize();
        $move = new Move($board, 'white', 'c1 h6');

        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move through another piece');
    }

}
