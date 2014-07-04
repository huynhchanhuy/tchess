<?php

namespace Tchess\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Bishop;
use Tchess\Entity\Piece\Queen;

class BishopRules implements EventSubscriberInterface
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onMoveChecking(MoveEvent $event)
    {
        $board = &$event->getGame()->getBoard();
        $move = $event->getMove();
        $color = $event->getColor();
        $piece = &$board->getPiece($move->getCurrentRow(), $move->getCurrentColumn());
        if (!$piece instanceof Bishop && !$piece instanceof Queen) {
            return;
        }


        if ($move->getCurrentRow() == $move->getNewRow() || $move->getCurrentColumn() == $move->getNewColumn()) {
            //Did not move diagonally
            $event->setValidMove(false);
            $event->stopPropagation();
            return;
        }

        if (abs($move->getNewRow() - $move->getCurrentRow()) != abs($move->getNewColumn() - $move->getCurrentColumn())) {
            $event->setValidMove(false);
            $event->stopPropagation();
            return;
        }

        if ($move->getCurrentRow() < $move->getNewRow()) {
            $rowOffset = 1;
        } else {
            $rowOffset = -1;
        }

        if ($move->getCurrentColumn() < $move->getNewColumn()) {
            $colOffset = 1;
        } else {
            $colOffset = -1;
        }

        $y = $move->getCurrentColumn() + $colOffset;
        for($x = $move->getCurrentRow() + $rowOffset; $x != $move->getNewRow(); $x += $rowOffset) {
            if($board->getPiece($x, $y) != null) {
                $event->setValidMove(false);
                $event->stopPropagation();
                return;
            }

            $y += $colOffset;
        }

        $event->setValidMove(true);
    }

    public static function getSubscribedEvents()
    {
        return array(
            MoveEvents::CHECH_MOVE => array(array('onMoveChecking', 0)),
        );
    }

}
