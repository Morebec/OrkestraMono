<?php

namespace Tests\Morebec\Orkestra\Messaging\Middleware;

use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageHandlerResponse;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Middleware\LoggerMiddleware;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

class LoggerMiddlewareTest extends TestCase
{
    public function testInvoke(): void
    {
        $logger = new TestLogger();
        $normalizer = $this->getMockBuilder(MessageNormalizerInterface::class)->getMock();

        $middleware = new LoggerMiddleware($logger, $normalizer);
        $headers = new MessageHeaders();
        $message = new class() implements MessageInterface {
            public static function getTypeName(): string
            {
                return 'test_message';
            }

            public function __toString(): string
            {
                return json_encode(['hello' => 'world']);
            }
        };

        $nextMiddleWare = function ($m, $h) {
            return new MessageHandlerResponse('handlerName', MessageBusResponseStatusCode::SUCCEEDED());
        };

        $middleware($message, $headers, $nextMiddleWare);

        $this->assertEquals($logger->records[0], [
            'level' => 'info',
            'message' => 'Received message "{messageTypeName}"',
            'context' => [
              'messageTypeName' => 'test_message',
              'messageType' => null,
              'message' => [
                'hello' => 'world',
              ],
              'messageHeaders' => [],
              'messageId' => null,
              'causationId' => null,
              'correlationId' => null,
            ],
          ]
        );
        $this->assertEquals($logger->records[1], [
            'level' => 'info',
            'message' => 'Received response "{responseStatusCode}" for message of type - "{messageTypeName}".',
            'context' => [
              'messageTypeName' => 'test_message',
              'messageType' => null,
              'message' => [
                'hello' => 'world',
              ],
              'messageHeaders' => [],
              'messageId' => null,
              'causationId' => null,
              'correlationId' => null,
              'responseStatusCode' => 'SUCCEEDED',
              'messageHandler' => 'handlerName',
            ],
          ]
        );
    }
}
