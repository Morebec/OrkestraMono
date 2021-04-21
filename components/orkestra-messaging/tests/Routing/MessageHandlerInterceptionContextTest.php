<?php

namespace Tests\Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHandlerInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Routing\MessageHandlerInterceptionContext;
use PHPUnit\Framework\TestCase;

class MessageHandlerInterceptionContextTest extends TestCase
{
    public function testReplaceResponse(): void
    {
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();
        $messageHandler = $this->getMockBuilder(MessageHandlerInterface::class)->getMock();

        $context = new MessageHandlerInterceptionContext(
            $message,
            new MessageHeaders(),
            $messageHandler,
            'method'
        );

        $response = $this->getMockBuilder(MessageBusResponseInterface::class)->getMock();
        $context->replaceResponse($response);

        $this->assertEquals($context->getResponse(), $response);
    }

    public function testReplaceMessageHandler(): void
    {
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();
        $messageHandler = $this->getMockBuilder(MessageHandlerInterface::class)->getMock();

        $context = new MessageHandlerInterceptionContext(
            $message,
            new MessageHeaders(),
            $messageHandler,
            'method'
        );

        $messageHandlerReplacer = $this->getMockBuilder(MessageHandlerInterface::class)->getMock();
        $context->replaceMessageHandler($messageHandlerReplacer, 'handlerMethod');

        $this->assertEquals($messageHandlerReplacer, $context->getMessageHandler());
        $this->assertEquals('handlerMethod', $context->getMessageHandlerMethodName());
    }

    public function testAddMessageHeaders(): void
    {
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();
        $messageHandler = $this->getMockBuilder(MessageHandlerInterface::class)->getMock();

        $context = new MessageHandlerInterceptionContext(
            $message,
            new MessageHeaders([
                'hello' => 'world',
            ]),
            $messageHandler,
            'method'
        );

        $context->addMessageHeaders(['foo' => 'bar']);

        $this->assertTrue($context->getMessageHeaders()->has('foo'));
        $this->assertEquals('bar', $context->getMessageHeaders()->get('foo'));

        // Replace
        $context->addMessageHeaders(['hello' => 'orkestra'], true);

        $this->assertTrue($context->getMessageHeaders()->has('hello'));
        $this->assertEquals('orkestra', $context->getMessageHeaders()->get('hello'));
    }

    public function testRemoveMessageHeaders(): void
    {
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();
        $messageHandler = $this->getMockBuilder(MessageHandlerInterface::class)->getMock();

        $context = new MessageHandlerInterceptionContext(
            $message,
            new MessageHeaders([
                'hello' => 'world',
            ]),
            $messageHandler,
            'method'
        );

        $context->removeMessageHeaders(['hello']);
        $this->assertFalse($context->getMessageHeaders()->has('hello'));
    }

    public function testGetMessageHandler(): void
    {
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();
        $messageHandler = $this->getMockBuilder(MessageHandlerInterface::class)->getMock();

        $context = new MessageHandlerInterceptionContext(
            $message,
            new MessageHeaders([
                'hello' => 'world',
            ]),
            $messageHandler,
            'method'
        );

        $this->assertEquals($messageHandler, $context->getMessageHandler());
    }

    public function testGetHandlerMethod(): void
    {
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();
        $messageHandler = $this->getMockBuilder(MessageHandlerInterface::class)->getMock();

        $context = new MessageHandlerInterceptionContext(
            $message,
            new MessageHeaders([
                'hello' => 'world',
            ]),
            $messageHandler,
            'method'
        );

        $this->assertEquals('method', $context->getMessageHandlerMethodName());
    }

    public function testGetMessage(): void
    {
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();
        $messageHandler = $this->getMockBuilder(MessageHandlerInterface::class)->getMock();

        $context = new MessageHandlerInterceptionContext(
            $message,
            new MessageHeaders([
                'hello' => 'world',
            ]),
            $messageHandler,
            'method'
        );

        $this->assertEquals($message, $context->getMessage());
    }

    public function testGetMessageHeaders(): void
    {
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();
        $messageHandler = $this->getMockBuilder(MessageHandlerInterface::class)->getMock();

        $messageHeaders = new MessageHeaders([
            'hello' => 'world',
        ]);
        $context = new MessageHandlerInterceptionContext(
            $message,
            $messageHeaders,
            $messageHandler,
            'method'
        );

        $this->assertEquals($messageHeaders, $context->getMessageHeaders());
    }

    public function testReplaceMessage(): void
    {
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();
        $messageHandler = $this->getMockBuilder(MessageHandlerInterface::class)->getMock();

        $context = new MessageHandlerInterceptionContext(
            $message,
            new MessageHeaders(),
            $messageHandler,
            'method'
        );

        $message = $this->getMockBuilder(MessageInterface::class)->getMock();
        $context->replaceMessage($message);

        $this->assertEquals($message, $context->getMessage());
    }

    public function testReplaceMessageHeaders(): void
    {
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();
        $messageHandler = $this->getMockBuilder(MessageHandlerInterface::class)->getMock();

        $context = new MessageHandlerInterceptionContext(
            $message,
            new MessageHeaders([
                'hello' => 'world',
            ]),
            $messageHandler,
            'method'
        );

        $replacedHeaders = new MessageHeaders();
        $context->replaceMessageHeaders($replacedHeaders);
        $this->assertEquals($replacedHeaders, $context->getMessageHeaders());
    }

    public function testGetResponse(): void
    {
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();
        $messageHandler = $this->getMockBuilder(MessageHandlerInterface::class)->getMock();
        $response = $this->getMockBuilder(MessageBusResponseInterface::class)->getMock();

        $context = new MessageHandlerInterceptionContext(
            $message,
            new MessageHeaders(),
            $messageHandler,
            'method'
        );

        $this->assertNull($context->getResponse());

        $context = new MessageHandlerInterceptionContext(
            $message,
            new MessageHeaders(),
            $messageHandler,
            'method',
            $response
        );

        $this->assertEquals($context->getResponse(), $response);
    }
}
