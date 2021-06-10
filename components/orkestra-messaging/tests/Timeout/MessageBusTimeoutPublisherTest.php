<?php

namespace Tests\Morebec\Orkestra\Messaging\Timeout;

use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageHandlerResponse;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\Timeout\MessageBusTimeoutPublisher;
use Morebec\Orkestra\Messaging\Timeout\TimeoutInterface;
use Morebec\Orkestra\Retry\RetryStrategy;
use PHPUnit\Framework\TestCase;

class MessageBusTimeoutPublisherTest extends TestCase
{
    public function testPublish(): void
    {
        $messageBus = $this->getMockBuilder(MessageBusInterface::class)->getMock();
        $publisher = new MessageBusTimeoutPublisher(
            $messageBus,
            RetryStrategy::create()
            ->maximumAttempts(3)
            ->retryAfterDelay(10)
            ->useExponentialBackoff()
        );

        $messageBus->method('sendMessage')->willReturn(
            new MessageHandlerResponse('handlerName', MessageBusResponseStatusCode::FAILED()),
        );

        $timeout = $this->getMockBuilder(TimeoutInterface::class)->getMock();

        $messageBus
            ->expects($this->exactly(4))
            ->method('sendMessage')
        ;

        $publisher->publish($timeout, new MessageHeaders());
    }
}
