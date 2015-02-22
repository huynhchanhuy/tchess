<?php

namespace Tchess\Tests\Rule;

use Tchess\Tests\UnitTestBase;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Board;

class InCheckRulesTest extends UnitTestBase
{
    public function testMoveEndUpInCheck()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnb1kbnr/pp1ppppp/8/3q4/8/7Q/PPP1PPPP/RNB1KBNR w KQkq - 2 5', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'e1 d1');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move the king end up in check');
    }

}
