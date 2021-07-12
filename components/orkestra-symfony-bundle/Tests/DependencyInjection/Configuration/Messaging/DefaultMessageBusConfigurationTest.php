<?php

namespace Tests\Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging;

use Baldinof\RoadRunnerBundle\Http\MiddlewareInterface;
use Morebec\Orkestra\Messaging\Authorization\AuthorizeMessageMiddleware;
use Morebec\Orkestra\Messaging\Authorization\MessageAuthorizerInterface;
use Morebec\Orkestra\Messaging\Context\BuildMessageBusContextMiddleware;
use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandHandlerInterface;
use Morebec\Orkestra\Messaging\Domain\DomainMessageHandlerInterface;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventHandlerInterface;
use Morebec\Orkestra\Messaging\Domain\Query\DomainQueryHandlerInterface;
use Morebec\Orkestra\Messaging\Middleware\LoggerMiddleware;
use Morebec\Orkestra\Messaging\Routing\MessageHandlerInterceptorInterface;
use Morebec\Orkestra\Messaging\Timeout\TimeoutHandlerInterface;
use Morebec\Orkestra\Messaging\Transformation\MessagingTransformerInterface;
use Morebec\Orkestra\Messaging\Validation\MessageValidatorInterface;
use Morebec\Orkestra\Messaging\Validation\ValidateMessageMiddleware;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging\MessageBusConfiguration;
use PHPUnit\Framework\TestCase;

class DefaultMessageBusConfigurationTest extends TestCase
{
    public function testWithMessageValidator(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->withMessageValidator(MessageValidatorInterface::class)
        ;

        self::assertContains(MessageValidatorInterface::class, $configuration->validators);
    }

    public function testWithMessageAuthorizer(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->withMessageAuthorizer(MessageAuthorizerInterface::class)
        ;

        self::assertContains(MessageAuthorizerInterface::class, $configuration->authorizers);
    }

    public function testWithMessageHandlerInterceptor(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->withMessageHandlerInterceptor(MessageHandlerInterceptorInterface::class)
        ;

        self::assertContains(MessageHandlerInterceptorInterface::class, $configuration->messageHandlerInterceptors);
    }

    public function testWithMessageTransformer(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->messagingTransformer(MessagingTransformerInterface::class)
        ;

        self::assertContains(MessagingTransformerInterface::class, $configuration->messagingTransformers);
    }

    public function testWithPrependedMiddleware(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->withMiddleware(ValidateMessageMiddleware::class)
            ->withPrependedMiddleware(LoggerMiddleware::class)
        ;

        self::assertEquals(LoggerMiddleware::class, $configuration->middleware[0]);
    }

    public function testWithMiddleware(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->withMiddleware(ValidateMessageMiddleware::class)
        ;

        self::assertContains(ValidateMessageMiddleware::class, $configuration->middleware);
    }

    public function testWithMiddlewareAfter(): void
    {
        $configuration = (new MessageBusConfiguration());

        $configuration->middleware = [];
        $configuration
            ->withMiddleware(BuildMessageBusContextMiddleware::class)
            ->withMiddleware(ValidateMessageMiddleware::class)
            ->withMiddleware(AuthorizeMessageMiddleware::class)
        ;

        $configuration
            ->withMiddlewareAfter(LoggerMiddleware::class, BuildMessageBusContextMiddleware::class)
        ;

        self::assertEquals([
            BuildMessageBusContextMiddleware::class,
            LoggerMiddleware::class,
            ValidateMessageMiddleware::class,
            AuthorizeMessageMiddleware::class,
        ], $configuration->middleware);

        // AFTER THE LAST ONE
        $configuration = (new MessageBusConfiguration());

        $configuration->middleware = [];

        $configuration
            ->withMiddleware(BuildMessageBusContextMiddleware::class)
            ->withMiddleware(ValidateMessageMiddleware::class)
            ->withMiddleware(AuthorizeMessageMiddleware::class)
        ;

        $configuration
            ->withMiddlewareAfter(LoggerMiddleware::class, AuthorizeMessageMiddleware::class)
        ;

        self::assertEquals([
            BuildMessageBusContextMiddleware::class,
            ValidateMessageMiddleware::class,
            AuthorizeMessageMiddleware::class,
            LoggerMiddleware::class,
        ], $configuration->middleware);

        $this->expectException(\InvalidArgumentException::class);
        $configuration
            ->withMiddlewareAfter(LoggerMiddleware::class, MiddlewareInterface::class)
        ;
    }

    public function testWithMiddlewareBefore(): void
    {
        $configuration = (new MessageBusConfiguration());

        $configuration->middleware = [];

        $configuration
            ->withMiddleware(BuildMessageBusContextMiddleware::class)
            ->withMiddleware(ValidateMessageMiddleware::class)
            ->withMiddleware(AuthorizeMessageMiddleware::class)
        ;

        $configuration
            ->withMiddlewareBefore(LoggerMiddleware::class, AuthorizeMessageMiddleware::class)
        ;

        self::assertEquals([
            BuildMessageBusContextMiddleware::class,
            ValidateMessageMiddleware::class,
            LoggerMiddleware::class,
            AuthorizeMessageMiddleware::class,
        ], $configuration->middleware);

        $this->expectException(\InvalidArgumentException::class);
        $configuration
            ->withMiddlewareAfter(LoggerMiddleware::class, MiddlewareInterface::class)
        ;
    }

    public function testCommandHandler(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->commandHandler(DomainCommandHandlerInterface::class)
        ;

        self::assertArrayHasKey(DomainCommandHandlerInterface::class, $configuration->messageHandlers);
    }

    public function testQueryHandler(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->queryHandler(DomainQueryHandlerInterface::class)
        ;

        self::assertArrayHasKey(DomainQueryHandlerInterface::class, $configuration->messageHandlers);
    }

    public function testEventHandler(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->eventHandler(DomainEventHandlerInterface::class)
        ;

        self::assertArrayHasKey(DomainEventHandlerInterface::class, $configuration->messageHandlers);
    }

    public function testTimeoutHandler(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->eventHandler(TimeoutHandlerInterface::class)
        ;

        self::assertArrayHasKey(TimeoutHandlerInterface::class, $configuration->messageHandlers);
    }

    public function testProcessManager(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->eventHandler(self::class)
        ;

        self::assertArrayHasKey(self::class, $configuration->messageHandlers);
    }

    public function testMessageHandler(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->eventHandler(DomainMessageHandlerInterface::class)
        ;

        self::assertArrayHasKey(DomainMessageHandlerInterface::class, $configuration->messageHandlers);
    }
}
