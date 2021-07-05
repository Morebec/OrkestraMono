<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\Messaging\Authorization\AuthorizeMessageMiddleware;
use Morebec\Orkestra\Messaging\MessageBus;
use Morebec\Orkestra\Messaging\Routing\HandleMessageMiddleware;
use Morebec\Orkestra\Messaging\Routing\LoggingMessageHandlerInterceptor;
use Morebec\Orkestra\Messaging\Validation\ValidateMessageMiddleware;

class DefaultMessageBusConfiguration extends MessageBusConfiguration
{
    public const DEFAULT_IMPLEMENTATION_CLASS_NAME = MessageBus::class;

    /** @var MessageBusHandlerConfiguration[] */
    public array $messageHandlers = [];

    /** @var string[] */
    public array $authorizers = [];

    /** @var string[] */
    public array $validators = [];

    /** @var string[] */
    public array $messageHandlerInterceptors = [];

    public function __construct()
    {
        parent::__construct();
        $this->usingImplementation(self::DEFAULT_IMPLEMENTATION_CLASS_NAME)
            ->withBuildMessageBusContextMiddleware()
            ->withLoggerMiddleware()
            ->withValidateMessageMiddleware()
            ->withAuthorizeMessageMiddleware()
            ->withRouteMessageMiddleware()
            ->withHandleMessageMiddleware()
            ->withMessageHandlerInterceptor(LoggingMessageHandlerInterceptor::class)
        ;
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
    public function commandHandler(string $serviceId, string $className = null, bool $autoroute = true): self
    {
        return $this->messageHandler($serviceId, $className, $autoroute);
    }

    /**
     * Configures a Query handler.
     */
    public function queryHandler(string $serviceId, string $className = null, bool $autoroute = true): self
    {
        return $this->messageHandler($serviceId, $className, $autoroute);
    }

    /**
     * Configures an Event handler.
     *
     * @return $this
     */
    public function eventHandler(string $serviceId, string $className = null, bool $autoroute = true): self
    {
        return $this->messageHandler($serviceId, $className, $autoroute);
    }

    /**
     * Configures a timeout handler.
     *
     * @return $this
     */
    public function timeoutHandler(string $serviceId, string $className = null, bool $autoroute = true): self
    {
        return $this->messageHandler($serviceId, $className, $autoroute);
    }

    /**
     * Configures a process manager.
     *
     * @return $this
     */
    public function processManager(string $serviceId, string $className = null, bool $autoroute = true): self
    {
        return $this->messageHandler($serviceId, $className, $autoroute);
    }

    /**
     * @return $this
     */
    public function messageHandler(string $serviceId, ?string $className, bool $autoroute): self
    {
        $this->messageHandlers[$serviceId] = new MessageBusHandlerConfiguration($serviceId, $className, $autoroute);

        return $this;
    }
}
