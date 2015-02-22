<?php

namespace Tchess\Tests\Rule;

use Tchess\Tests\UnitTestBase;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Board;
use Tchess\Event\MoveEvent;
use Tchess\MoveEvents;

class BishopRulesTest extends UnitTestBase
{
    /**
     * Is used only on valid test case.
     *
     * @var Board Board object
     */
    protected static $board;

    public function testNotMoveDiagonally()
    {
        /* @var $board Board */
        $board = $this->serializer->deserialize('rnbqkbnr/pppppp1p/6p1/8/3P4/8/PPP1PPPP/RNBQKBNR w KQkq - 0 2', 'Tchess\Entity\Board', 'fen');

        $move = new Move($board, 'white', 'c1 f3');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Did not move diagonally');

        $move = new Move($board, 'white', 'e1 d3');
        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move like a knight');
    }

    public function testGoThroughAnotherPiece()
    {
        $board = new Board();
        $board->initialize();
        $move = new Move($board, 'white', 'c1 h6');

        $errors = $this->validator->validate($move);
        $this->assertTrue(count($errors) > 0, 'Can not move through another piece');
    }

    /**
     * @dataProvider validMoveDataProvider
     */
    public function testValidMove($color, $source, $target)
    {
        if (!isset(static::$board)) {
            static::$board = $this->serializer->deserialize('rnbqkbnr/1ppppppp/p7/8/8/6P1/PPPPPP1P/RNBQKBNR w KQkq - 0 2', 'Tchess\Entity\Board', 'fen');
        }
        $move = new Move(static::$board, $color, "$source $target");
        $errors = $this->validator->validate($move);
        $this->assertEquals(0, count($errors), 'Valid move');

        static::$board->movePiece($source, $target);

        $moveEvent = new MoveEvent(0, $move);
        $this->dispatcher->dispatch(MoveEvents::MOVE, $moveEvent);
    }

    public function validMoveDataProvider()
    {
        return array(
          array('white', 'f1', 'h3'),
          array('black', 'b7', 'b6'),
          array('white', 'h3', 'd7'),
          array('black', 'c8', 'd7'),
        );
    }

}
