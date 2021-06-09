<?php

namespace Morebec\Orkestra\Messaging\Timeout;

use Morebec\Orkestra\Messaging\MessageHeaders;

/**
 * This wrapper class is used to group together a timeout and headers for persistence purposes.
 */
class TimeoutWrapper
{
    /** @var string */
    private $id;

    /** @var TimeoutInterface */
    private $timeout;

    /** @var MessageHeaders */
    private $headers;

    private function __construct(TimeoutInterface $timeout, MessageHeaders $headers)
    {
        $this->id = $timeout->getId();
        $this->headers = $headers;
        $this->timeout = $timeout;
    }

    public static function wrap(TimeoutInterface $timeout, MessageHeaders $headers): self
    {
        return new self($timeout, $headers);
    }

    public function getTimeout(): TimeoutInterface
    {
        return $this->timeout;
    }

    public function getMessageHeaders(): MessageHeaders
    {
        return $this->headers;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getHeaders(): MessageHeaders
    {
        return $this->headers;
    }
}
