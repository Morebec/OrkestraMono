<?php

namespace Tests\Morebec\OrkestraSymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\Messaging\Authorization\MessageAuthorizerInterface;
use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandHandlerInterface;
use Morebec\Orkestra\Messaging\Domain\DomainMessageHandlerInterface;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventHandlerInterface;
use Morebec\Orkestra\Messaging\Domain\Query\DomainQueryHandlerInterface;
use Morebec\Orkestra\Messaging\Routing\MessageHandlerInterceptorInterface;
use Morebec\Orkestra\Messaging\Timeout\TimeoutHandlerInterface;
use Morebec\Orkestra\Messaging\Transformation\MessagingTransformerInterface;
use Morebec\Orkestra\Messaging\Validation\MessageValidatorInterface;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\DefaultMessageBusConfiguration;
use PHPUnit\Framework\TestCase;

class DefaultMessageBusConfigurationTest extends TestCase
{
    public function testWithMessageValidator(): void
    {
        $configuration = (new DefaultMessageBusConfiguration())
            ->withMessageValidator(MessageValidatorInterface::class)
        ;

        self::assertContains(MessageValidatorInterface::class, $configuration->validators);
    }

    public function testWithMessageAuthorizer(): void
    {
        $configuration = (new DefaultMessageBusConfiguration())
            ->withMessageAuthorizer(MessageAuthorizerInterface::class)
        ;

        self::assertContains(MessageAuthorizerInterface::class, $configuration->authorizers);
    }

    public function testWithMessageHandlerInterceptor(): void
    {
        $configuration = (new DefaultMessageBusConfiguration())
            ->withMessageHandlerInterceptor(MessageHandlerInterceptorInterface::class)
        ;

        self::assertContains(MessageHandlerInterceptorInterface::class, $configuration->messageHandlerInterceptors);
    }

    public function testWithMessageTransformer(): void
    {
        $configuration = (new DefaultMessageBusConfiguration())
            ->messagingTransformer(MessagingTransformerInterface::class)
        ;

        self::assertContains(MessagingTransformerInterface::class, $configuration->messagingTransformers);
    }

    public function testCommandHandler(): void
    {
        $configuration = (new DefaultMessageBusConfiguration())
            ->commandHandler(DomainCommandHandlerInterface::class)
        ;

        self::assertArrayHasKey(DomainCommandHandlerInterface::class, $configuration->messageHandlers);
    }

    public function testQueryHandler(): void
    {
        $configuration = (new DefaultMessageBusConfiguration())
            ->queryHandler(DomainQueryHandlerInterface::class)
        ;

        self::assertArrayHasKey(DomainQueryHandlerInterface::class, $configuration->messageHandlers);
    }

    public function testEventHandler(): void
    {
        $configuration = (new DefaultMessageBusConfiguration())
            ->eventHandler(DomainEventHandlerInterface::class)
        ;

        self::assertArrayHasKey(DomainEventHandlerInterface::class, $configuration->messageHandlers);
    }

    public function testTimeoutHandler(): void
    {
        $configuration = (new DefaultMessageBusConfiguration())
            ->eventHandler(TimeoutHandlerInterface::class)
        ;

        self::assertArrayHasKey(TimeoutHandlerInterface::class, $configuration->messageHandlers);
    }

    public function testProcessManager(): void
    {
        $configuration = (new DefaultMessageBusConfiguration())
            ->eventHandler(self::class)
        ;

        self::assertArrayHasKey(self::class, $configuration->messageHandlers);
    }

    public function testMessageHandler(): void
    {
        $configuration = (new DefaultMessageBusConfiguration())
            ->eventHandler(DomainMessageHandlerInterface::class)
        ;

        self::assertArrayHasKey(DomainMessageHandlerInterface::class, $configuration->messageHandlers);
    }
}
