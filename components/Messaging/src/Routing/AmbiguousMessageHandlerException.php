<?php

namespace Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\MessageInterface;
use Throwable;

/**
 * Thrown when a message type required exactly one handler but more were found (e.g. Commands and Queries).
 */
class AmbiguousMessageHandlerException extends \LogicException
{
    public function __construct(MessageInterface $message, Throwable $previous = null)
    {
        parent::__construct(
            "Multiple Message Handlers found for message {$message::getTypeName()}, expected 1.",
            0,
            $previous
        );
    }
}
