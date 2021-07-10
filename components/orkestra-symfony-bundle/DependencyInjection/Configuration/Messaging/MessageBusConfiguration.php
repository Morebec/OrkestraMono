<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging;

use Morebec\Orkestra\Messaging\Authorization\AuthorizeMessageMiddleware;
use Morebec\Orkestra\Messaging\Context\BuildMessageBusContextMiddleware;
use Morebec\Orkestra\Messaging\MessageBus;
use Morebec\Orkestra\Messaging\Middleware\LoggerMiddleware;
use Morebec\Orkestra\Messaging\Routing\HandleMessageMiddleware;
use Morebec\Orkestra\Messaging\Routing\RouteMessageMiddleware;
use Morebec\Orkestra\Messaging\Transformation\MessagingTransformationMiddleware;
use Morebec\Orkestra\Messaging\Validation\ValidateMessageMiddleware;

/**
 * Configures the dependencies of a message bus.
 */
class MessageBusConfiguration
{
    public string $serviceId;

    public string $implementationClassName;

    /** @var string[] */
    public array $middleware;

    public ?MessageNormalizerConfiguration $messageNormalizerConfiguration;

    public function __construct()
    {
        $this->middleware = [];
        $this->usingDefaultImplementation();
    }

    public static function defaultConfiguration(): DefaultMessageBusConfiguration
    {
        return new DefaultMessageBusConfiguration();
    }

    public function usingServiceId(string $serviceId): self
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * Allows specifying that the implementation of the interface is the default {@link MessageBus}.
     *
     * @return $this
     */
    public function usingDefaultImplementation(): self
    {
        $this->implementationClassName = DefaultMessageBusConfiguration::DEFAULT_IMPLEMENTATION_CLASS_NAME;

        return $this;
    }

    /**
     * Allows specifying the implementation to use for the {@link MessageBusInterface}.
     *
     * @return $this
     */
    public function usingImplementation(string $className): self
    {
        $this->implementationClassName = $className;

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
     * Configures the message bus to use the {@link BuildMessageBusContextMiddleware}.
     *
     * @return $this
     */
    public function withBuildMessageBusContextMiddleware(): self
    {
        return $this->withMiddleware(BuildMessageBusContextMiddleware::class);
    }

    /**
     * Configures the message bus to use the {@link LoggerMiddleware}.
     *
     * @return $this
     */
    public function withLoggerMiddleware(): self
    {
        return $this->withMiddleware(LoggerMiddleware::class);
    }

    /**
     * Configures the message bus to use the {@link HandleMessageMiddleware}.
     *
     * @return $this
     */
    public function withRouteMessageMiddleware(): self
    {
        return $this->withMiddleware(RouteMessageMiddleware::class);
    }

    /**
     * Configures this message bus to use the {@link MessagingTransformationMiddleware}.
     *
     * @return $this
     */
    public function withMessagingTransformationMiddleware(): self
    {
        return $this->withMiddleware(MessagingTransformationMiddleware::class);
    }

    /**
     * Configures the message bus to use the {@link AuthorizeMessageMiddleware}.
     *
     * @return $this
     */
    public function withAuthorizeMessageMiddleware(): self
    {
        return $this->withMiddleware(AuthorizeMessageMiddleware::class);
    }

    /**
     * Configures the message bus to use the {@link ValidateMessageMiddleware}.
     *
     * @return $this
     */
    public function withValidateMessageMiddleware(): self
    {
        return $this->withMiddleware(ValidateMessageMiddleware::class);
    }

    /**
     * Configures the message bus to use the {@link HandleMessageMiddleware}.
     *
     * @return $this
     */
    public function withHandleMessageMiddleware(): self
    {
        return $this->withMiddleware(HandleMessageMiddleware::class);
    }

    /**
     * Replaces a middleware in this configuration.
     *
     * @return $this
     */
    public function withMiddlewareReplacedBy(string $replacedMiddlewareClassName, string $substituteMiddlewareClassName): self
    {
        foreach ($this->middleware as $index => $m) {
            if ($m === $replacedMiddlewareClassName) {
                $this->middleware[$index] = $substituteMiddlewareClassName;
            }
        }

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
     * Allows removing a middleware.
     *
     * @return $this
     */
    public function withoutMiddleware(string $middlewareClassName): self
    {
        $this->middleware = array_filter($this->middleware, static function (string $className) use ($middlewareClassName) {
            return $className !== $middlewareClassName;
        });

        return $this;
    }
}
