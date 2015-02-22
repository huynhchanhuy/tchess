<?php

namespace Tchess\Tests\Rule;

use Tchess\Tests\UnitTestBase;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Board;

class RookRulesTest extends UnitTestBase
{
    public function testDidNotMoveAlongOneRowOrColumn()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/2pppppp/pp6/8/7P/7R/PPPPPPP1/RNBQKBN1 w Qkq - 0 3', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'h3 c4');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Did not move along one row or column');
    }

    public function testMoveThroughPieces()
    {

        $board = new Board();
        $board->initialize();

        $move = new Move($board, 'white', 'h1 h5');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move through pieces');

        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/ppppp3/5ppp/8/7P/5P1R/PPPPP1P1/RNBQKBN1 w Qkq - 0 4', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'h3 c3');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move through pieces');
    }

    public function testValidMove()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/pppppp2/6pp/8/7P/7R/PPPPPPP1/RNBQKBN1 w Qkq - 0 3', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'h3 a3');
        $errors = $this->validator->validate($move);
        $this->assertEquals(0, count($errors), 'Valid move');
    }

}
