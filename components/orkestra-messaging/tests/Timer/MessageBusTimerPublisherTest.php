<?php

namespace Tests\Morebec\Orkestra\Messaging\Timer;

use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageHandlerResponse;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\Timer\MessageBusTimerPublisher;
use Morebec\Orkestra\Messaging\Timer\TimerInterface;
use Morebec\Orkestra\Retry\RetryStrategy;
use PHPUnit\Framework\TestCase;

class MessageBusTimerPublisherTest extends TestCase
{
    public function testPublish(): void
    {
        $messageBus = $this->getMockBuilder(MessageBusInterface::class)->getMock();
        $publisher = new MessageBusTimerPublisher(
            $messageBus,
            RetryStrategy::create()
            ->maximumAttempts(3)
            ->retryAfterDelay(10)
            ->useExponentialBackoff()
        );

        $messageBus->method('sendMessage')->willReturn(
            new MessageHandlerResponse('handlerName', MessageBusResponseStatusCode::FAILED()),
        );

        $timer = $this->getMockBuilder(TimerInterface::class)->getMock();

        $messageBus
            ->expects($this->exactly(4))
            ->method('sendMessage')
        ;

        $publisher->publish($timer, new MessageHeaders());
    }
}
