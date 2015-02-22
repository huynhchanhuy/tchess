<?php

namespace Tchess\Tests\Rule;

use Tchess\Tests\UnitTestBase;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Board;
use Tchess\Event\MoveEvent;
use Tchess\MoveEvents;

class BasicRulesTest extends UnitTestBase
{
    public function testMoveToTheSameSquare()
    {
        $board = new Board();
        $board->initialize();

        $move = new Move($board, 'white', 'a1 a1');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move piece to the same square');
    }

    public function testMoveEmptySquare()
    {
        $board = new Board();
        $board->initialize();

        $move = new Move($board, 'white', 'a3 a4');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'There are no piece at a3');
    }

    public function testMoveOpponentPiece()
    {
        $board = new Board();
        $board->initialize();

        $move = new Move($board, 'white', 'a7 a6');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move opponent piece');
    }

    public function testTakeOwnPiece()
    {
        $board = new Board();
        $board->initialize();

        $move = new Move($board, 'white', 'a1 a2');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not take your own piece');
    }

    /**
     * @dataProvider activeColorDataProvider
     */
    public function testActiveColor($board, $color, $source, $target, $expected)
    {
        $board->movePiece($source, $target);
        $move = new Move($board, $color, "$source $target");
        $moveEvent = new MoveEvent(0, $move);
        $this->dispatcher->dispatch(MoveEvents::MOVE, $moveEvent);
        $this->assertEquals($expected, $board->getActiveColor(), "$color's turn");
    }

    public function activeColorDataProvider()
    {
        $board = new Board();
        $board->initialize();

        $this->assertEquals('white', $board->getActiveColor(), 'Init active color');

        return array(
          array($board, 'white', 'a2', 'a3', 'black'),
          array($board, 'black', 'g8', 'h6', 'white'),
          array($board, 'white', 'd2', 'd4', 'black'),
          array($board, 'black', 'b8', 'c6', 'white'),
          array($board, 'white', 'c1', 'h6', 'black'),
          array($board, 'black', 'c6', 'd4', 'white'),
        );
    }

    /**
     * @dataProvider fullmoveDataProvider
     */
    public function testFullmoveNumber($board, $color, $source, $target, $expected)
    {
        $board->movePiece($source, $target);
        $move = new Move($board, $color, "$source $target");
        $moveEvent = new MoveEvent(0, $move);
        $this->dispatcher->dispatch(MoveEvents::MOVE, $moveEvent);
        $this->assertEquals($expected, $board->getFullmoveNumber(), "After $color move");
    }

    public function fullmoveDataProvider()
    {
        $board = new Board();
        $board->initialize();

        $this->assertEquals(1, $board->getFullmoveNumber(), 'Init fullmove number');

        return array(
          array($board, 'white', 'a2', 'a3', 1),
          array($board, 'black', 'a7', 'a5', 2),
          array($board, 'white', 'b2', 'b3', 2),
          array($board, 'black', 'a5', 'a4', 3),
        );
    }

}
