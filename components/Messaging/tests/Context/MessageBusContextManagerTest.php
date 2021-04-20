<?php

namespace Tests\Morebec\Orkestra\Messaging\Context;

use Morebec\Orkestra\Messaging\Context\MessageBusContextManager;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use PHPUnit\Framework\TestCase;

class MessageBusContextManagerTest extends TestCase
{
    public function testManageContext(): void
    {
        $manager = new MessageBusContextManager();

        $manager->startContext($this->getMessage(), new MessageHeaders([
            MessageHeaders::CORRELATION_ID => 'corr',
            MessageHeaders::CAUSATION_ID => null,
            MessageHeaders::MESSAGE_ID => 'messageId',
        ]));

        $manager->startContext($this->getMessage(), new MessageHeaders([
            MessageHeaders::CORRELATION_ID => 'corr',
            MessageHeaders::CAUSATION_ID => null,
            MessageHeaders::MESSAGE_ID => 'messageId',
        ]));

        $manager->endContext();

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
