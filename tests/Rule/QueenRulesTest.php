<?php

namespace Tchess\Tests\Rule;

use Tchess\Tests\UnitTestBase;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Board;

class QueenRulesTest extends UnitTestBase
{
    public function testInvalidMove()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/pppppp2/6pp/8/4P3/5Q2/PPPP1PPP/RNB1KBNR w KQkq - 0 3', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'f3 c4');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Invalid queen move');
    }

}
