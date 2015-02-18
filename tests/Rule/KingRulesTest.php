<?php

namespace Tchess\Tests\Rule;

use Tchess\Tests\UnitTestBase;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Board;

class KingRulesTest extends UnitTestBase
{
    public function testMoveMoreThanOneRow()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/ppppp3/5ppp/8/3PPP2/8/PPP3PP/RNBQKBNR w KQkq - 0 4', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'e1 e3');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move like a rook');

        $move = new Move($board, 'white', 'e1 f3');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move like a knight');

        $move = new Move($board, 'white', 'e1 g3');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move like a bishop');

        $move = new Move($board, 'white', 'e1 h3');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move to a square that no piece type can reach');
    }

}
