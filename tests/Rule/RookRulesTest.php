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

}
