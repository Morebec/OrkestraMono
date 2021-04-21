<?php

namespace Morebec\Orkestra\Messaging\Middleware;

use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Represents a middleware to allow injecting logic inside the Message Bus.
 */
interface MessageBusMiddlewareInterface
{
    /**
     * Handles a given {@link MessageInterface} according to this middlewares logic and calls next.
     *
     * @param callable $next callable with a signature taking a {@link MessageInterface} and {@link MessageHeaders} as its arguments
     */
    public function __invoke(MessageInterface $message, MessageHeaders $headers, callable $next): MessageBusResponseInterface;
}
