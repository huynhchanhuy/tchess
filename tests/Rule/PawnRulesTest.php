<?php

namespace Tchess\Tests\Rule;

use Tchess\Tests\UnitTestBase;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Board;

class PawnRulesTest extends UnitTestBase
{
    public function testMoveBackward()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/1ppppppp/p7/8/8/P7/1PPPPPPP/RNBQKBNR w KQkq - 0 2', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'a3 a2');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move a pawn backward');
    }

    public function testTakeAPieceInFrontOfIt()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/1ppppppp/8/p7/P7/8/1PPPPPPP/RNBQKBNR w KQkq a6 0 2', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'a4 a5');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not take a piece in front of it');
    }

}
