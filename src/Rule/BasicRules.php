<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Piece;
use Tchess\Rule\MoveCheckerInterface;
use Tchess\Entity\Piece\Move;

class BasicRules implements EventSubscriberInterface, MoveCheckerInterface
{
    public function checkMove(Move $move)
    {
        $board = $move->getBoard();
        $color = $move->getColor();
        list($currentRow, $currentColumn) = Move::getIndex($move->getSource());
        list($newRow, $newColumn) = Move::getIndex($move->getTarget());

        if ($currentRow == $newRow && $currentColumn == $newColumn) {
            return 'Can not move to the same square';
        }

        $source_piece = $board->getPiece($currentRow, $currentColumn);
        $target_piece = $board->getPiece($newRow, $newColumn);

        if ($source_piece == null) {
            return 'Can not move empty square';
        }

        if (!$source_piece instanceof Piece || $source_piece->getColor() != $color) {
            return 'Can not move opponent piece';
        }

        if ($target_piece instanceof Piece && $target_piece->getColor() == $color) {
            return 'Can not take your own piece';
        }
    }

    public function onMoveUpdateFullmoveNumber(MoveEvent $event)
    {
        $move = $event->getMove();
        $board = $move->getBoard();
        $color = $move->getColor();
        if ($color == 'black') {
            // Incremented after Black's move.
            $board->increaseFullmoveNumber();
        }
    }

    public function onMoveUpdateActiveColor(MoveEvent $event)
    {
        $move = $event->getMove();
        $board = $move->getBoard();
        $color = $move->getColor();
        $board->setActiveColor($color == 'white' ? 'black' : 'white');
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::MOVE => array(
                array('onMoveUpdateFullmoveNumber', 0),
                array('onMoveUpdateActiveColor', -1),
            ),
        );
    }

    public static function getRules()
    {
        return array(array('checkMove', 1));
    }

}
