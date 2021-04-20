<?php

namespace Morebec\Orkestra\Messaging\Middleware;

use Morebec\Orkestra\Messaging\MessageBusExceptionInterface;
use Morebec\Orkestra\Messaging\MessageInterface;
use Throwable;

/**
 * Exception thrown by the Message Bus when no middleware returned a response for a given message.
 * This indicates misconfiguration in the middleware pipeline.
 * TODO: Move to Middleware namespace.
 */
final class NoResponseFromMiddlewareException extends \RuntimeException implements MessageBusExceptionInterface
{
    public function __construct(MessageInterface $message, Throwable $previous = null)
    {
        $exceptionMessage = sprintf('No Middleware returned a response for message of type: "%s".', $message::getTypeName());
        parent::__construct($exceptionMessage, 0, $previous);
    }
}
