<?php

namespace Tchess\Event;

use Tchess\Entity\Game;
use Symfony\Component\EventDispatcher\Event;
use Tchess\Entity\Piece\Move;

class MoveEvent extends Event
{

    private $game;
    private $move;
    private $validMove;
    private $message;
    private $color;

    public function __construct(Game $game, $move, $color)
    {
        $this->setGame($game);
        $this->setMove($move);
        $this->setColor($color);
        $this->validMove = false;
    }

    public function getGame()
    {
        return $this->game;
    }

    public function setGame($game)
    {
        $this->game = $game;
    }

    public function getMove()
    {
        return $this->move;
    }

    public function setMove(Move $move)
    {
        $this->move = $move;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        $this->color = $color;
    }

    public function isValidMove()
    {
        return $this->validMove;
    }

    public function setValidMove($validMove)
    {
        $this->validMove = $validMove;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

}
