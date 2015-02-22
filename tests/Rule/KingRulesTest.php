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

        $move = new Move($board, 'white', 'e1 g1');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not do (kingside) castling while in check');
    }

    public function testDoCastlingEndUpInCheck()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('1nbqkbn1/2ppppp1/2r3r1/p7/3PP3/Np1BBQ1p/P4P1P/R3K2R w KQ - 0 12', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'e1 c1');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not do (queenside) castling end up in check');

        $move = new Move($board, 'white', 'e1 g1');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not do (kingside) castling end up in check');
    }

    public function testDoCastlingWhileRooksAreMoved()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/7p/ppppppp1/8/PPP5/N7/RBQPPPPP/4KBNR w Kkq - 0 8', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'e1 c1');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not do castling if queenside rook is moved');

        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/pp6/2pppppp/8/6PP/7N/PPPPPPB1/RNBQK2R w Qkq - 0 7', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'e1 g1');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not do castling if kingside rook is moved');
    }

    public function testDoCastlingWhileThereArePiecesBetweenKingAndRook()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/3ppppp/ppp5/6B1/3P4/N7/PPP1PPPP/R2QKBNR w KQkq - 0 4', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'e1 c1');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not do castling if there is a piece between king and rook');

        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/3ppppp/ppp5/8/3P1B2/3Q4/PPP1PPPP/RN2KBNR w KQkq - 0 4', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'e1 c1');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not do castling if there is a piece between king and rook');

        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/1ppppppp/p7/8/8/7N/PPPPPPPP/RNBQKB1R w KQkq - 0 2', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'e1 g1');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not do castling if there is a piece between king and rook');
    }

    public function testValidMove()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/1ppppppp/p7/8/3P4/8/PPP1PPPP/RNBQKBNR w KQkq - 0 2', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'e1 d2');
        $errors = $this->validator->validate($move);
        $this->assertEquals(0, count($errors), 'Valid move');
    }

    public function testValidQueensideCastlingMove()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/5ppp/ppppp3/8/1PP5/N7/PBQPPPPP/R3KBNR w KQkq - 0 6', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'e1 c1');
        $errors = $this->validator->validate($move);
        $this->assertEquals(0, count($errors), 'Valid queenside castling move');
    }

    public function testValidKingsideCastlingMove()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/ppppp3/5ppp/8/6P1/7N/PPPPPPBP/RNBQK2R w KQkq - 0 4', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'e1 g1');
        $errors = $this->validator->validate($move);
        $this->assertEquals(0, count($errors), 'Valid kingside castling move');
    }

}
