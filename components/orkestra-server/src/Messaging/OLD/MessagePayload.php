<?php

namespace Morebec\Orkestra\OrkestraServer\Messaging;

class MessagePayload
{
    /**
     * @var mixed
     */
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
