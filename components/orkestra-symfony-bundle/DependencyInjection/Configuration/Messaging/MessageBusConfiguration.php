<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging;

use Morebec\Orkestra\Messaging\Authorization\AuthorizeMessageMiddleware;
use Morebec\Orkestra\Messaging\MessageBus;
use Morebec\Orkestra\Messaging\Routing\HandleMessageMiddleware;
use Morebec\Orkestra\Messaging\Transformation\MessagingTransformerInterface;
use Morebec\Orkestra\Messaging\Validation\ValidateMessageMiddleware;

class MessageBusConfiguration
{
    /** @var MessageBusHandlerConfiguration[] */
    public array $messageHandlers = [];

    /** @var string[] */
    public array $authorizers = [];

    /** @var string[] */
    public array $validators = [];

    /** @var string[] */
    public array $messageHandlerInterceptors = [];

    /** @var string[] */
    public array $messagingTransformers = [];

    /** @var string[] */
    public array $middleware = [];

    public string $serviceId;

    public function __construct()
    {
        $this->serviceId = MessageBus::class;
    }

    public function usingServiceId(string $serviceId): self
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * Prepends middleware to the current list of middleware.
     *
     * @return $this
     */
    public function withPrependedMiddleware(string $middlewareClassName): self
    {
        array_unshift($this->middleware, $middlewareClassName);

        return $this;
    }

    /**
     * Allows to append a middleware at the end of the list of middleware.
     *
     * @return $this
     */
    public function withMiddleware(string $middlewareClassName): self
    {
        $this->middleware[] = $middlewareClassName;

        return $this;
    }

    /**
     * Configures the message bus to use a middleware and order it right after a given preceding one.
     *
     * @return $this
     */
    public function withMiddlewareAfter(string $middleware, string $precedingMiddleware): self
    {
        $foundIndex = null;
        foreach ($this->middleware as $key => $m) {
            if ($m === $precedingMiddleware) {
                $foundIndex = $key;
                break;
            }
        }

        if ($foundIndex === null) {
            throw new \InvalidArgumentException("Middleware \"$precedingMiddleware\" was not found.");
        }

        array_splice($this->middleware, $foundIndex + 1, 0, $middleware);

        return $this;
    }

    /**
     * Configures the message bus to use a middleware and order it right before a given following one.
     *
     * @return $this
     */
    public function withMiddlewareBefore(string $middleware, string $followingMiddleware): self
    {
        $foundIndex = null;
        foreach ($this->middleware as $key => $m) {
            if ($m === $followingMiddleware) {
                $foundIndex = $key;
                break;
            }
        }

        if ($foundIndex === null) {
            throw new \InvalidArgumentException("Middleware \"$followingMiddleware\" was not found.");
        }

        array_splice($this->middleware, $foundIndex, 0, $middleware);

        return $this;
    }

    /**
     * Registers a message validator with the message bus with the {@link ValidateMessageMiddleware}.
     *
     * @return $this
     */
    public function withMessageValidator(string $className): self
    {
        $this->validators[] = $className;

        return $this;
    }

    /**
     * Registers a Message Authorizer with the validator with the {@link AuthorizeMessageMiddleware}.
     *
     * @return $this
     */
    public function withMessageAuthorizer(string $className): self
    {
        $this->authorizers[] = $className;

        return $this;
    }

    /**
     * Configures a {@link MessagingTransformerInterface}.
     *
     * @return $this
     */
    public function messagingTransformer(string $className): self
    {
        $this->messagingTransformers[] = $className;

        return $this;
    }

    /**
     * Configures a message handler.
     *
     * @return $this
     */
    public function messageHandler(string $serviceId, ?string $className): self
    {
        $this->messageHandlers[$serviceId] = new MessageBusHandlerConfiguration($serviceId, $className);

        return $this;
    }

    /**
     * Registers a message handler interceptor with the {@link HandleMessageMiddleware}.
     *
     * @return $this
     */
    public function withMessageHandlerInterceptor(string $className): self
    {
        $this->messageHandlerInterceptors[] = $className;

        return $this;
    }

    /**
     * Configures a Command handler.
     */
    public function commandHandler(string $serviceId, string $className = null): self
    {
        return $this->messageHandler($serviceId, $className);
    }

    /**
     * Configures a Query handler.
     */
    public function queryHandler(string $serviceId, string $className = null): self
    {
        return $this->messageHandler($serviceId, $className);
    }

    /**
     * Configures an Event handler.
     *
     * @return $this
     */
    public function eventHandler(string $serviceId, string $className = null): self
    {
        return $this->messageHandler($serviceId, $className);
    }

    /**
     * Configures a timeout handler.
     *
     * @return $this
     */
    public function timeoutHandler(string $serviceId, string $className = null): self
    {
        return $this->messageHandler($serviceId, $className);
    }

    /**
     * Configures a process manager.
     *
     * @return $this
     */
    public function processManager(string $serviceId, string $className = null): self
    {
        return $this->messageHandler($serviceId, $className);
    }
}
