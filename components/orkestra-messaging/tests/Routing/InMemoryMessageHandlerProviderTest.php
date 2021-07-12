<?php

namespace Tests\Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\MessageHandlerInterface;
use Morebec\Orkestra\Messaging\Routing\InMemoryMessageHandlerProvider;
use PHPUnit\Framework\TestCase;

class InMemoryMessageHandlerProviderTest extends TestCase
{
    public function testGetMessageHandler(): void
    {
        $messageHandler = $this->getMockBuilder(MessageHandlerInterface::class)->getMock();
        $provider = new InMemoryMessageHandlerProvider([
            $messageHandler,
        ]);

        self::assertNull($provider->getMessageHandler('not_there'));

        self::assertEquals($messageHandler, $provider->getMessageHandler(\get_class($messageHandler)));
    }

    public function testAddMessageHandler(): void
    {
        $provider = new InMemoryMessageHandlerProvider();

        $messageHandler = $this->getMockBuilder(MessageHandlerInterface::class)->getMock();
        $provider->addMessageHandler($messageHandler);

        self::assertEquals($messageHandler, $provider->getMessageHandler(\get_class($messageHandler)));
    }
}
