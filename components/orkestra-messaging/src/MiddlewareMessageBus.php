<?php

namespace Morebec\Orkestra\Messaging;

use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareCollection;
use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareInterface;
use Morebec\Orkestra\Messaging\Middleware\NoResponseFromMiddlewareException;

/**
 * Implementation of a Message Bus that relies on Middleware to augment its behaviour.
 */
class MiddlewareMessageBus implements MessageBusInterface
{
    private MessageBusMiddlewareCollection $middleware;

    public function __construct(iterable $middleware = [])
    {
        $this->middleware = new MessageBusMiddlewareCollection($middleware);
        $this->replaceMiddleware($middleware);
    }

    public function sendMessage(MessageInterface $message, ?MessageHeaders $headers = null): MessageBusResponseInterface
    {
        $next = $this->createCallableForNextMiddleware(0);

        if (!$headers) {
            $headers = new MessageHeaders();
        }

        return $next($message, $headers);
    }

    /**
     * Appends new middleware to this message bus.
     */
    public function appendMiddleware(MessageBusMiddlewareInterface $middleware): void
    {
        $this->middleware->append($middleware);
    }

    /**
     * Prepends new middleware to this message bus.
     */
    public function prependMiddleware(MessageBusMiddlewareInterface $middleware): void
    {
        $this->middleware->prepend($middleware);
    }

    /**
     * Completely replaces the middleware of this message bus.
     *
     * @param MessageBusMiddlewareInterface[] $middleware
     */
    public function replaceMiddleware(iterable $middleware): void
    {
        $this->middleware = new MessageBusMiddlewareCollection($middleware);
    }

    /**
     * Returns the middleware of this message bus.
     */
    public function getMiddleware(): MessageBusMiddlewareCollection
    {
        return $this->middleware;
    }

    /**
     * Creates a callable for a middleware at a given index. (the $next parameter).
     */
    protected function createCallableForNextMiddleware(int $currentMiddlewareIndex): callable
    {
        $middleware = $this->middleware->getOrDefault($currentMiddlewareIndex);

        // If we are past all the middleware, throw a default response, this would mean that no middleware decided to return a response.
        if (!$middleware) {
            return static function (MessageInterface $message, MessageHeaders $headers): MessageBusResponseInterface {
                throw new NoResponseFromMiddlewareException($message);
            };
        }

        return fn (MessageInterface $message, MessageHeaders $headers): MessageBusResponseInterface => $middleware($message, $headers, $this->createCallableForNextMiddleware($currentMiddlewareIndex + 1))
            ;
    }
}
