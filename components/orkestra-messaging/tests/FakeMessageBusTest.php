<?php

namespace Tests\Morebec\Orkestra\Messaging;

use Morebec\Orkestra\Messaging\AbstractMessageBusResponse;
use Morebec\Orkestra\Messaging\FakeMessageBus;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageInterface;
use PHPUnit\Framework\TestCase;

class FakeMessageBusTest extends TestCase
{
    public function testChangeResponse(): void
    {
        $expectedResponse = new class() extends AbstractMessageBusResponse {
            public function __construct()
            {
                parent::__construct(MessageBusResponseStatusCode::INVALID(), null);
            }
        };

        $bus = new FakeMessageBus();
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();

        $bus->changeResponse($expectedResponse);

        $actualResponse = $bus->sendMessage($message);

        self::assertEquals($expectedResponse, $actualResponse);
    }

    public function testSendMessage(): void
    {
        $expectedResponse = new class() extends AbstractMessageBusResponse {
            public function __construct()
            {
                parent::__construct(MessageBusResponseStatusCode::INVALID(), null);
            }
        };

        $bus = new FakeMessageBus($expectedResponse);
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();

        $actualResponse = $bus->sendMessage($message);

        self::assertEquals($expectedResponse, $actualResponse);
    }

    public function testGetCollectedMessages(): void
    {
        $bus = new FakeMessageBus();
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();

        $bus->sendMessage($message);

        self::assertContains($message, $bus->getCollectedMessages());
    }
}
