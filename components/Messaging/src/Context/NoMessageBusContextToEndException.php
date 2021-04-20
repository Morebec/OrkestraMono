<?php

namespace Morebec\Orkestra\Messaging\Context;

/**
 * Exception thrown when there is no context to be ended.
 */
class NoMessageBusContextToEndException extends \LogicException
{
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct('There is no context to be ended in the Message Bus.', 0, $previous);
    }
}
