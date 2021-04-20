<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventStore;

use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\EventSourcing\EventStore\AppendStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\EventData;
use Morebec\Orkestra\EventSourcing\EventStore\EventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\EventId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventType;
use Morebec\Orkestra\EventSourcing\EventStore\InMemoryEventStore;
use Morebec\Orkestra\EventSourcing\EventStore\MessageBusContextEventStoreDecorator;
use Morebec\Orkestra\EventSourcing\EventStore\ReadStreamOptions;
use Morebec\Orkestra\Messaging\Context\MessageBusContext;
use Morebec\Orkestra\Messaging\Context\MessageBusContextProvider;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use PHPUnit\Framework\TestCase;

class MessageBusContextEventStoreDecoratorTest extends TestCase
{
    public function testAppendToStream(): void
    {
        /** @var EventStoreInterface $eventStore */
        $eventStore = new InMemoryEventStore(new SystemClock());

        /** @var MessageBusContextProvider $contextProvider */
        $contextProvider = $this->getMockBuilder(MessageBusContextProvider::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        /** @var MessageInterface $message */
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();

        $contextProvider->method('getContext')->willReturn(new MessageBusContext($message, new MessageHeaders([
            MessageHeaders::MESSAGE_ID => 'test_message',
            MessageHeaders::CORRELATION_ID => 'test_correlation',
            MessageHeaders::APPLICATION_ID => 'test_app',
            MessageHeaders::TENANT_ID => 'test_tenant',
            MessageHeaders::USER_ID => 'test_user',
        ])));

        $store = new MessageBusContextEventStoreDecorator($eventStore, $contextProvider);

        $event = new EventDescriptor(
            EventId::fromString('event1'),
            EventType::fromString('user_account_registered'),
            new EventData([
                'username' => 'user_1',
                'emailAddress' => 'email1@address.com',
            ])
        );
        $streamId = EventStreamId::fromString('test_stream');
        $store->appendToStream($streamId, [$event], AppendStreamOptions::append());
        $event = $store->readStream($streamId, ReadStreamOptions::lastEvent())->getFirst();

        $this->assertEquals('test_message', $event->getEventMetadata()->getValue(MessageHeaders::CAUSATION_ID));
        $this->assertEquals('test_correlation', $event->getEventMetadata()->getValue(MessageHeaders::CORRELATION_ID));
        $this->assertEquals('test_app', $event->getEventMetadata()->getValue(MessageHeaders::APPLICATION_ID));
        $this->assertEquals('test_tenant', $event->getEventMetadata()->getValue(MessageHeaders::TENANT_ID));
        $this->assertEquals('test_user', $event->getEventMetadata()->getValue(MessageHeaders::USER_ID));
    }
}
