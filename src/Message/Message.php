<?php

namespace Tchess\Message;

class Message implements \JsonSerializable
{
    protected $roomId;
    protected $action;
    protected $data = array();

    public function __construct($roomId, $action, $data)
    {
        $this->setRoomId($roomId);
        $this->setAction($action);
        $this->setData($data);
    }

    public function getRoomId()
    {
        return $this->roomId;
    }

    public function setRoomId($roomId)
    {
        $this->roomId = $roomId;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function jsonSerialize() {
        return array(
            'room' => $this->roomId,
            'action' => $this->action,
            'data' => $this->data
        );
    }
}
