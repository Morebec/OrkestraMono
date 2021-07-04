<?php

namespace Tests\Morebec\OrkestraSymfonyBundle\Module;

use Morebec\Orkestra\Messaging\Authorization\AuthorizeMessageMiddleware;
use Morebec\Orkestra\Messaging\MessageBus;
use Morebec\Orkestra\Messaging\Middleware\LoggerMiddleware;
use Morebec\Orkestra\Messaging\Validation\ValidateMessageMiddleware;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\DefaultMessageBusConfiguration;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\MessageBusConfiguration;
use PHPUnit\Framework\TestCase;

class MessageBusConfigurationTest extends TestCase
{
    public function testWithPrependedMiddleware(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->withMiddleware(ValidateMessageMiddleware::class)
            ->withPrependedMiddleware(LoggerMiddleware::class)
        ;

        self::assertEquals([
            LoggerMiddleware::class,
            ValidateMessageMiddleware::class,
        ], $configuration->middleware);
    }

    public function testUsingImplementation(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->usingImplementation(MessageBus::class)
        ;

        self::assertEquals(MessageBus::class, $configuration->implementationClassName);
    }

    public function testWithMiddleware(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->withMiddleware(ValidateMessageMiddleware::class)
        ;

        self::assertContains(ValidateMessageMiddleware::class, $configuration->middleware);
    }

    public function testUsingDefaultImplementation(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->usingDefaultImplementation()
        ;

        $this->assertEquals(DefaultMessageBusConfiguration::DEFAULT_IMPLEMENTATION_CLASS_NAME, $configuration->implementationClassName);
    }

    public function testReplaceMiddleware(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->withMiddleware(ValidateMessageMiddleware::class)
            ->withMiddlewareReplacedBy(ValidateMessageMiddleware::class, AuthorizeMessageMiddleware::class)
        ;

        self::assertEquals([
            AuthorizeMessageMiddleware::class,
        ], $configuration->middleware);
    }

    public function testWithoutMiddleware(): void
    {
        $configuration = (new MessageBusConfiguration())
            ->withMiddleware(ValidateMessageMiddleware::class)
            ->withoutMiddleware(ValidateMessageMiddleware::class)
        ;

        $this->assertNotContains(ValidateMessageMiddleware::class, $configuration->middleware);
    }
}
