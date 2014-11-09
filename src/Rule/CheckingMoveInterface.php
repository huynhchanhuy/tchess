<?php

namespace Tchess\Rule;

use Tchess\Event\MoveEvent;

interface CheckingMoveInterface
{
    public function checkMove(MoveEvent $event);
}
