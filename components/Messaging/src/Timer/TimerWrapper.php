<?php

namespace Morebec\Orkestra\Messaging\Timer;

use Morebec\Orkestra\Messaging\MessageHeaders;

/**
 * This wrapper class is used to group together a timer and headers for persistence purposes.
 */
class TimerWrapper
{
    /** @var string */
    private $id;

    /** @var TimerInterface */
    private $timer;

    /** @var MessageHeaders */
    private $headers;

    private function __construct(TimerInterface $timer, MessageHeaders $headers)
    {
        $this->id = $timer->getId();
        $this->headers = $headers;
        $this->timer = $timer;
    }

    public static function wrap(TimerInterface $timer, MessageHeaders $headers): self
    {
        return new self($timer, $headers);
    }

    public function getTimer(): TimerInterface
    {
        return $this->timer;
    }

    public function getMessageHeaders(): MessageHeaders
    {
        return $this->headers;
    }
}
