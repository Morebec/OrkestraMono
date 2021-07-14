<?php

namespace Morebec\Orkestra\Messaging;

use RuntimeException;
use Throwable;

/**
 * Exception encompassing multiple exceptions as thrown during event handling.
 */
class MultiMessageHandlerException extends RuntimeException
{
    /** @var Throwable[] */
    private array $throwables;

    public function __construct(array $throwables, $message = 'An exception occurred.')
    {
        parent::__construct($message, 0, $throwables[0]);
        $this->message = $message;
        $this->throwables = $throwables;
    }

    /**
     * @return Throwable[]
     */
    public function getThrowables(): array
    {
        return $this->throwables;
    }
}
