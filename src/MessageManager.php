<?php

namespace Tchess;

use Tchess\Message\Message;

class MessageManager
{

    /**
     * @var Message[]
     */
    protected $messages = array();

    /**
     * Add move.
     *
     * @param Move $message
     */
    public function addMessage(Message $message)
    {
        $this->messages[] = $message;
    }

    /**
     * Get moves.
     *
     * @return Move[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

}
