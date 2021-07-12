<?php

namespace Tests\Morebec\Orkestra\Messaging\Middleware;

use Morebec\Orkestra\Messaging\Middleware\LoggerMiddleware;
use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareCollection;
use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\Messaging\Routing\MessageRouter;
use Morebec\Orkestra\Messaging\Routing\RouteMessageMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MessageBusMiddlewareCollectionTest extends TestCase
{
    public function testToArray(): void
    {
        $expectedArray = [
            $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock(),
            $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock(),
            $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock(),
        ];
        $collection = new MessageBusMiddlewareCollection($expectedArray);

        self::assertEquals($expectedArray, $collection->toArray());
    }

    public function testCount(): void
    {
        $collection = new MessageBusMiddlewareCollection([
            $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock(),
            $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock(),
            $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock(),
        ]);

        self::assertCount(3, $collection);
    }

    public function testAddBefore(): void
    {
        $a = new LoggerMiddleware(
            $this->getMockBuilder(LoggerInterface::class)->getMock(),
            $this->getMockBuilder(MessageNormalizerInterface::class)->getMock()
        );
        $b = new RouteMessageMiddleware(new MessageRouter());
        $c = $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock();

        $collection = new MessageBusMiddlewareCollection([
            $a, $c,
        ]);

        $collection->addBefore(\get_class($c), $b);

        self::assertEquals($b, $collection->getOrDefault(1));
    }

    public function testAddAfter(): void
    {
        $a = new LoggerMiddleware(
            $this->getMockBuilder(LoggerInterface::class)->getMock(),
            $this->getMockBuilder(MessageNormalizerInterface::class)->getMock()
        );
        $b = new RouteMessageMiddleware(new MessageRouter());
        $c = $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock();

        $collection = new MessageBusMiddlewareCollection([
            $a, $c,
        ]);

        $collection->addAfter(\get_class($a), $b);

        self::assertEquals($b, $collection->getOrDefault(1));
    }

    public function testGetOrDefault(): void
    {
        $middleware = $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock();
        $collection = new MessageBusMiddlewareCollection([$middleware]);

        self::assertEquals($middleware, $collection->getOrDefault(0));
        self::assertNull($collection->getOrDefault(5));
    }

    public function testAppend(): void
    {
        $a = new LoggerMiddleware(
            $this->getMockBuilder(LoggerInterface::class)->getMock(),
            $this->getMockBuilder(MessageNormalizerInterface::class)->getMock()
        );
        $b = new RouteMessageMiddleware(new MessageRouter());
        $c = $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock();

        $collection = new MessageBusMiddlewareCollection([
            $a, $b,
        ]);
        $collection->append($c);

        $this->assertEquals($c, $collection->getOrDefault(2));
    }

    public function testPrepend(): void
    {
        $a = new LoggerMiddleware(
            $this->getMockBuilder(LoggerInterface::class)->getMock(),
            $this->getMockBuilder(MessageNormalizerInterface::class)->getMock()
        );
        $b = new RouteMessageMiddleware(new MessageRouter());
        $c = $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock();

        $collection = new MessageBusMiddlewareCollection([
            $b, $c,
        ]);
        $collection->prepend($a);

        self::assertEquals($a, $collection->getOrDefault(0));
    }

    public function testIterator(): void
    {
        $collection = new MessageBusMiddlewareCollection([
            $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock(),
            $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock(),
            $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock(),
        ]);

        $counter = 0;
        foreach ($collection as $middleware) {
            self::assertInstanceOf(MessageBusMiddlewareInterface::class, $middleware);
            $counter++;
        }

        self::assertEquals(3, $counter);
    }
}
