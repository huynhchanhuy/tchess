<?php

namespace Tchess\Tests\Rule;

use Tchess\Tests\UnitTestBase;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Board;
use Tchess\Entity\Piece\Queen;
use Tchess\Event\MoveEvent;
use Tchess\MoveEvents;

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

    public function testMoveMoreThan2Rows()
    {
        $board = new Board();
        $board->initialize();

        $move = new Move($board, 'white', 'a2 a5');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move more than 2 rows');
    }

    public function testMove2RowsIfMoved()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/1ppppppp/p7/8/8/P7/1PPPPPPP/RNBQKBNR w KQkq - 0 2', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'a3 a5');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move 2 rows if the pawn is moved');
    }

    public function testTakePieceWhileAdvancing2RowsAtBeginning()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/1ppppppp/8/8/p6P/8/PPPPPPP1/RNBQKBNR w KQkq - 0 3', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'a2 a4');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not take a piece while advancing 2 rows at beginning');
    }

    public function testInvalidTakingMove()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/ppppppp1/7p/8/8/P7/1PPPPPPP/RNBQKBNR w KQkq - 0 2', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'a3 c5');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Invalid taking move');
    }

    public function testTakingNoPieceInEmptySquare()
    {
        $board = new Board();
        $board->initialize();

        $move = new Move($board, 'white', 'a2 b3');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Take no piece in empty square');
    }

    public function testInvalidCaptureEnPassantMove()
    {

        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/p1pppppp/8/Pp6/8/8/1PPPPPPP/RNBQKBNR w KQkq - 0 3', 'Tchess\Entity\Board', 'fen');

        // Pawn is not epable.
        $move = new Move($board, 'white', 'a5 b6');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Invalid capture en passant move');

        /* @var $board Board */
        $board = $this->serializer->deserialize('r1bqkbnr/pppppppp/8/3Pn3/8/8/PPP1PPPP/RNBQKBNR w KQkq - 1 3', 'Tchess\Entity\Board', 'fen');

        // Not a pawn.
        $move = new Move($board, 'white', 'd5 e6');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Invalid capture en passant move');

        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/pppppp2/6pp/P7/8/8/1PPPPPPP/RNBQKBNR w KQkq - 0 3', 'Tchess\Entity\Board', 'fen');

        // Empty square.
        $move = new Move($board, 'white', 'a5 b6');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Invalid capture en passant move');

        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/pppp4/4pppp/PP6/8/8/2PPPPPP/RNBQKBNR w KQkq - 0 5', 'Tchess\Entity\Board', 'fen');

        // Same color pawn.
        $move = new Move($board, 'white', 'a5 b6');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Invalid capture en passant move');

    }

    public function testValidMove()
    {
        $board = new Board();
        $board->initialize();

        $move = new Move($board, 'white', 'a2 a3');
        $errors = $this->validator->validate($move);
        $this->assertEquals(0, count($errors), 'Valid move');
    }

    public function testDoQueening()
    {
        $source = 'b7';
        $target = 'a8';
        $color = 'white';
        $board = $this->serializer->deserialize('rn1qkbnr/1Ppppppp/8/8/8/8/1PPPPPPP/RNBQKBNR b KQkq - 0 4', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, $color, "$source $target");
        $errors = $this->validator->validate($move);
        $this->assertEquals(0, count($errors), 'Valid move');

        $board->movePiece($source, $target);

        $moveEvent = new MoveEvent(0, $move);
        $this->dispatcher->dispatch(MoveEvents::MOVE, $moveEvent);

        list($currentRow, $currentColumn) = Move::getIndex($source);
        $this->assertNull($board->getPiece($currentRow, $currentColumn));

        list($newRow, $newColumn) = Move::getIndex($target);
        $queen = $board->getPiece($newRow, $newColumn);
        $this->assertTrue($queen instanceof Queen, 'Pawn is promoted to a queen.');
        $this->assertEquals('white', $queen->getColor(), 'Queen is of white player.');
    }

}
