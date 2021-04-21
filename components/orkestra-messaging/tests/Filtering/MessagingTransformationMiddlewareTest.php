<?php

namespace Tests\Morebec\Orkestra\Messaging\Filtering;

use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageHandlerResponse;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Transformation\MessagingTransformationMiddleware;
use Morebec\Orkestra\Messaging\Transformation\MessagingTransformerInterface;
use PHPUnit\Framework\TestCase;

class MessagingTransformationMiddlewareTest extends TestCase
{
    public function testInvoke(): void
    {
        $middleware = new MessagingTransformationMiddleware([
            $this->createTransformer(),
        ]);

        $finalMessage = null;
        $nextMiddleware = static function (MessageInterface $message, MessageHeaders $headers) use (&$finalMessage) {
            $finalMessage = $message;

            return new MessageHandlerResponse('handlerName', MessageBusResponseStatusCode::SUCCEEDED());
        };

        $message = $this->getMockBuilder(MessageInterface::class)->getMock();
        $response = $middleware($message, new MessageHeaders(), $nextMiddleware);

        $this->assertNotInstanceOf(MessageHandlerResponse::class, $response);
        $this->assertNotNull($finalMessage);
    }

    private function createTransformer(): MessagingTransformerInterface
    {
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();
        $response = $this->getMockBuilder(MessageBusResponseInterface::class)->getMock();

        return new class($message, $response) implements MessagingTransformerInterface {
            /**
             * @var MessageInterface
             */
            private $message;
            /**
             * @var MessageBusResponseInterface
             */
            private $response;

            public function __construct(MessageInterface $message, MessageBusResponseInterface $response)
            {
                $this->message = $message;
                $this->response = $response;
            }

            public function transformMessage(MessageInterface $message, MessageHeaders $headers): MessageInterface
            {
                return $this->message;
            }

            public function transformResponse(MessageBusResponseInterface $response, MessageInterface $message, MessageHeaders $headers): MessageBusResponseInterface
            {
                return $this->response;
            }
        };
    }
}
