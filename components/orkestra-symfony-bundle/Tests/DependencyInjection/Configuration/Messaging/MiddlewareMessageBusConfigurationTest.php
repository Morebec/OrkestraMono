<?php

namespace Tests\Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging;

use Baldinof\RoadRunnerBundle\Http\MiddlewareInterface;
use Morebec\Orkestra\Messaging\Authorization\AuthorizeMessageMiddleware;
use Morebec\Orkestra\Messaging\Context\BuildMessageBusContextMiddleware;
use Morebec\Orkestra\Messaging\Middleware\LoggerMiddleware;
use Morebec\Orkestra\Messaging\Validation\ValidateMessageMiddleware;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging\MiddlewareMessageBusConfiguration;
use PHPUnit\Framework\TestCase;

class MiddlewareMessageBusConfigurationTest extends TestCase
{
    public function testWithMiddleware(): void
    {
        $configuration = (new MiddlewareMessageBusConfiguration())
            ->withMiddleware(ValidateMessageMiddleware::class)
        ;

        self::assertContains(ValidateMessageMiddleware::class, $configuration->middleware);
    }

    public function testReplaceMiddleware(): void
    {
        $configuration = (new MiddlewareMessageBusConfiguration());

        $configuration->middleware = [];

        $configuration
            ->withMiddleware(ValidateMessageMiddleware::class)
            ->withMiddlewareReplacedBy(ValidateMessageMiddleware::class, AuthorizeMessageMiddleware::class)
        ;

        self::assertEquals([
            AuthorizeMessageMiddleware::class,
        ], $configuration->middleware);
    }

    public function testWithoutMiddleware(): void
    {
        $configuration = (new MiddlewareMessageBusConfiguration())
            ->withMiddleware(ValidateMessageMiddleware::class)
            ->withoutMiddleware(ValidateMessageMiddleware::class)
        ;

        $this->assertNotContains(ValidateMessageMiddleware::class, $configuration->middleware);
    }

    public function testWithMiddlewareAfter(): void
    {
        $configuration = (new MiddlewareMessageBusConfiguration());

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
        $configuration = (new MiddlewareMessageBusConfiguration());

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
        $configuration = (new MiddlewareMessageBusConfiguration());

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
}
