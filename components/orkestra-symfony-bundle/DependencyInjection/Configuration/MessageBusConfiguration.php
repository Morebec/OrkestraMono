<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\Messaging\Authorization\AuthorizeMessageMiddleware;
use Morebec\Orkestra\Messaging\Context\BuildMessageBusContextMiddleware;
use Morebec\Orkestra\Messaging\MessageBus;
use Morebec\Orkestra\Messaging\Middleware\LoggerMiddleware;
use Morebec\Orkestra\Messaging\Routing\HandleMessageMiddleware;
use Morebec\Orkestra\Messaging\Routing\RouteMessageMiddleware;
use Morebec\Orkestra\Messaging\Validation\ValidateMessageMiddleware;

class MessageBusConfiguration
{
    public string $implementationClassName;

    /** @var string[] */
    public array $middleware;

    public function __construct()
    {
        $this->middleware = [];
        $this->usingDefaultImplementation();
    }

    public static function defaultConfiguration(): DefaultMessageBusConfiguration
    {
        return new DefaultMessageBusConfiguration();
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
