<?php

namespace Morebec\Orkestra\Messaging;

use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareInterface;
use Morebec\Orkestra\Messaging\Middleware\NoResponseFromMiddlewareException;

class MessageBus implements MessageBusInterface
{
    /**
     * @var MessageBusMiddlewareInterface[]
     */
    private $middleware;

    public function __construct(iterable $middleware = [])
    {
        $this->middleware = [];
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

    public function appendMiddleware(MessageBusMiddlewareInterface $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function prependMiddleware(MessageBusMiddlewareInterface $middleware): void
    {
        array_unshift($this->middleware, $middleware);
    }

    public function replaceMiddleware(iterable $middleware): void
    {
        $this->middleware = [];
        foreach ($middleware as $m) {
            $this->appendMiddleware($m);
        }
    }

    public function getMiddleware(): iterable
    {
        return $this->middleware;
    }

    /**
     * Creates a callable for a middleware at a given index. (the $next parameter).
     */
    protected function createCallableForNextMiddleware(int $currentMiddlewareIndex): callable
    {
        // If we are past all the middleware, throw a default response, this would mean that no middleware decided to return a response.
        if (!\array_key_exists($currentMiddlewareIndex, $this->middleware)) {
            return static function (MessageInterface $message, MessageHeaders $headers): MessageBusResponseInterface {
                throw new NoResponseFromMiddlewareException($message);
            };
        }

        $middleware = $this->middleware[$currentMiddlewareIndex];

        $self = $this;

        return function (MessageInterface $message, MessageHeaders $headers) use ($self, $currentMiddlewareIndex, $middleware): MessageBusResponseInterface {
            $nextCallable = $self->createCallableForNextMiddleware($currentMiddlewareIndex + 1);

            /* @var MessageBusMiddlewareInterface $middleware */
            return $middleware($message, $headers, $nextCallable);
        };
    }
}
