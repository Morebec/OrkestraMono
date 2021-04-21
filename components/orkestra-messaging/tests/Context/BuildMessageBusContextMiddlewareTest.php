<?php

namespace Tests\Morebec\Orkestra\Messaging\Context;

use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\Messaging\Context\BuildMessageBusContextMiddleware;
use Morebec\Orkestra\Messaging\Context\MessageBusContextManager;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageHandlerResponse;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use PHPUnit\Framework\TestCase;

class BuildMessageBusContextMiddlewareTest extends TestCase
{
    public function testInvoke(): void
    {
        $manager = new MessageBusContextManager();
        $clock = new SystemClock();
        $middleware = new BuildMessageBusContextMiddleware($clock, $manager);

        $message = $this->getMessage();
        $middleware($message, new MessageHeaders(), static function ($m, $h) {
            return new MessageHandlerResponse('handler', MessageBusResponseStatusCode::SUCCEEDED());
        });

        // Nested Message
        $this->assertNull($manager->getContext());

        $manager->startContext($this->getMessage(), new MessageHeaders([
            MessageHeaders::CORRELATION_ID => 'corr',
            MessageHeaders::CAUSATION_ID => null,
            MessageHeaders::MESSAGE_ID => 'messageId',
        ]));
        $middleware($message, new MessageHeaders(), static function ($a, $b) {
            return new MessageHandlerResponse('handler', MessageBusResponseStatusCode::SUCCEEDED());
        });
        $this->assertNotNull($manager->getContext());
    }

    private function getMessage(): MessageInterface
    {
        return new class() implements MessageInterface {
            public static function getTypeName(): string
            {
                return 'test';
            }
        };
    }
}
