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
        $this->assertTrue(count($errors) > 0, 'Can not move to a square that no piece type can reach in one move');
    }

    public function testKingMoved()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/ppp5/3ppppp/8/2PPPP2/8/PP2K1PP/RNBQ1BNR w kq - 0 6', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'e2 c2');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move 2 squares if the king is moved');
    }

    public function testBothRooksMoved()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/8/p7/1ppppppp/PPPPPPPP/R1N2N1R/1BQ3B1/4K3 w kq - 0 16', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'e1 c1');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move 2 squares if both rooks are moved');

        $move = new Move($board, 'white', 'e1 g1');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move 2 squares if both rooks are moved');
    }

    public function testInvalidCastlingMove()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/pp6/2pppppp/8/1PP5/N1B5/P1QPPPPP/R3KBNR w KQkq - 0 7', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'e1 b1');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move more than 2 columns while castling');

        $move = new Move($board, 'white', 'e1 b2');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move to different row while castling');
    }

    public function testDoCastlingWhileInCheck()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnb1kbnr/p7/1ppp1ppp/1P6/2Ppq1P1/N6N/PBQ2PBP/R3K2R w KQkq - 0 12', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'e1 c1');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not do (queenside) castling while in check');

        $move = new Move($board, 'white', 'e1 g2');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not do (kingside) castling while in check');
    }

}
