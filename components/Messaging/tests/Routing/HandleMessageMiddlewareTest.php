<?php

namespace Tests\Morebec\Orkestra\Privacy\Routing;

use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageHandlerInterface;
use Morebec\Orkestra\Messaging\MessageHandlerResponse;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Routing\HandleMessageMiddleware;
use Morebec\Orkestra\Messaging\Routing\MessageHandlerInterceptionContext;
use Morebec\Orkestra\Messaging\Routing\MessageHandlerInterceptorInterface;
use Morebec\Orkestra\Messaging\Routing\MessageHandlerProviderInterface;
use Morebec\Orkestra\Messaging\Routing\UnhandledMessageResponse;
use PHPUnit\Framework\TestCase;

class HandleMessageMiddlewareTest extends TestCase
{
    public function testInvoke(): void
    {
        $handlerProvider = $this->getMockBuilder(MessageHandlerProviderInterface::class)->getMock();

        $messageHandler = $this->createMessageHandler();

        $handlerProvider->method('getMessageHandler')->willReturn($messageHandler);

        $middleware = new HandleMessageMiddleware($handlerProvider, [
            $this->createInterceptor($messageHandler),
        ]);

        // This should call the willFail method, but the interceptor should change this to the willSucceed method.
        $headers = new MessageHeaders([
            MessageHeaders::DESTINATION_HANDLER_NAMES => ['somHandler::willFail'],
        ]);
        $nextMiddleware = static function (MessageInterface $message, MessageHeaders $headers) {
            return new MessageHandlerResponse('handlerName', MessageBusResponseStatusCode::SUCCEEDED());
        };

        $message = $this->createMessage();
        $response = $middleware($message, $headers, $nextMiddleware);

        $this->assertEquals($response->getStatusCode(), MessageBusResponseStatusCode::SUCCEEDED());
    }

    public function testInvokeWithNoHandler()
    {
        $handlerProvider = $this->getMockBuilder(MessageHandlerProviderInterface::class)->getMock();

        $middleware = new HandleMessageMiddleware($handlerProvider);
        $headers = new MessageHeaders();
        $nextMiddleware = static function (MessageInterface $message, MessageHeaders $headers) {
            return new MessageHandlerResponse('handlerName', MessageBusResponseStatusCode::SUCCEEDED());
        };

        $message = $this->createMessage();
        $response = $middleware($message, $headers, $nextMiddleware);

        $this->assertInstanceOf(UnhandledMessageResponse::class, $response);
        $this->assertEquals($response->getStatusCode(), MessageBusResponseStatusCode::SKIPPED());
    }

    private function createMessage(): MessageInterface
    {
        return new class() implements MessageInterface {
            /** @var string */
            public $property;

            public static function getTypeName(): string
            {
                return 'message';
            }
        };
    }

    private function createMessageHandler(): MessageHandlerInterface
    {
        return new class() implements MessageHandlerInterface {
            public function willFail(MessageInterface $message): MessageBusResponseStatusCode
            {
                return MessageBusResponseStatusCode::FAILED();
            }

            public function willSucceed(MessageInterface $message): MessageBusResponseStatusCode
            {
                return MessageBusResponseStatusCode::SUCCEEDED();
            }
        };
    }

    private function createInterceptor(MessageHandlerInterface $handler)
    {
        return new class($handler) implements MessageHandlerInterceptorInterface {
            /**
             * @var MessageHandlerInterface
             */
            private $handler;

            public function __construct(MessageHandlerInterface $handler)
            {
                $this->handler = $handler;
            }

            public function beforeHandle(MessageHandlerInterceptionContext $context): void
            {
                $context->replaceMessageHandler($this->handler, 'willSucceed');
            }

            public function afterHandle(MessageHandlerInterceptionContext $context): void
            {
            }
        };
    }
}
