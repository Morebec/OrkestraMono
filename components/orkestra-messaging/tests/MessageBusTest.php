<?php

namespace Tests\Morebec\Orkestra\Messaging;

use Morebec\Orkestra\Messaging\MessageBus;
use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageHandlerResponse;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareInterface;
use PHPUnit\Framework\TestCase;

class MessageBusTest extends TestCase
{
    public function testSendMessage(): void
    {
        $middlewares = [
            $this->createMiddlewareA(),
            $this->createMiddlewareB(),
            $this->createMiddlewareC(),
        ];

        $bus = new MessageBus($middlewares);

        $message = $this->getMockBuilder(MessageInterface ::class)->getMock();
        /** @var MessageInterface $message */
        $response = $bus->sendMessage($message, new MessageHeaders());

        $this->assertTrue($response->isSuccess());
    }

    public function testPrependMiddleware(): void
    {
        $middlewareA = $this->createMiddlewareA();
        $middlewareB = $this->createMiddlewareB();

        $bus = new MessageBus([$middlewareA]);
        $bus->prependMiddleware($middlewareB);

        $middleware = $bus->getMiddleware();

        $this->assertEquals($middleware[0], $middlewareB);
        $this->assertEquals($middleware[1], $middlewareA);
    }

    public function testAppendMiddleware(): void
    {
        $middlewareA = $this->createMiddlewareA();
        $middlewareB = $this->createMiddlewareB();

        $bus = new MessageBus([$middlewareA]);
        $bus->appendMiddleware($middlewareB);

        $middleware = $bus->getMiddleware();

        $this->assertEquals($middleware[0], $middlewareA);
        $this->assertEquals($middleware[1], $middlewareB);
    }

    public function testGetMiddleware(): void
    {
        $bus = new MessageBus();
        $this->assertEmpty($bus->getMiddleware());
        $bus->appendMiddleware($this->createMiddlewareA());
        $this->assertNotEmpty($bus->getMiddleware());
    }

    public function testReplaceMiddleware(): void
    {
        $bus = new MessageBus([$this->createMiddlewareA()]);
        $middlewareB = $this->createMiddlewareB();
        $middlewareC = $this->createMiddlewareC();

        $bus->replaceMiddleware([
            $middlewareB,
            $middlewareC,
        ]);

        $middleware = $bus->getMiddleware();

        $this->assertEquals($middleware[0], $middlewareB);
        $this->assertEquals($middleware[1], $middlewareC);
    }

    public function createMiddlewareA(): MessageBusMiddlewareInterface
    {
        return new class() implements MessageBusMiddlewareInterface {
            public function __invoke(MessageInterface $message, MessageHeaders $headers, callable $next): MessageBusResponseInterface
            {
                return $next($message, $headers);
            }
        };
    }

    public function createMiddlewareB(): MessageBusMiddlewareInterface
    {
        return new class() implements MessageBusMiddlewareInterface {
            public function __invoke(MessageInterface $message, MessageHeaders $headers, callable $next): MessageBusResponseInterface
            {
                return $next($message, $headers);
            }
        };
    }

    public function createMiddlewareC(): MessageBusMiddlewareInterface
    {
        return new class() implements MessageBusMiddlewareInterface {
            public function __invoke(MessageInterface $message, MessageHeaders $headers, callable $next): MessageBusResponseInterface
            {
                return new MessageHandlerResponse('test_command_handler', MessageBusResponseStatusCode::SUCCEEDED());
            }
        };
    }
}
