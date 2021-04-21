<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventStore;

use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\EventSourcing\EventStore\AppendStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\EventData;
use Morebec\Orkestra\EventSourcing\EventStore\EventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\EventId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreSubscriberInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamNotFoundException;
use Morebec\Orkestra\EventSourcing\EventStore\EventType;
use Morebec\Orkestra\EventSourcing\EventStore\InMemoryEventStore;
use Morebec\Orkestra\EventSourcing\EventStore\ReadStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\SubscriptionOptions;
use PHPUnit\Framework\TestCase;

class InMemoryEventStoreTest extends TestCase
{
    /**
     * @var InMemoryEventStore
     */
    private $store;

    protected function setUp(): void
    {
        $this->store = new InMemoryEventStore(new SystemClock());
        $this->store->clear();
    }

    protected function tearDown(): void
    {
        $this->store->clear();
    }

    public function testReadStreamThrowsExceptionWhenStreamDoesNotExist(): void
    {
        $streamId = EventStreamId::fromString('stream-not-found');
        $this->expectException(EventStreamNotFoundException::class);
        $this->store->readStream($streamId, new ReadStreamOptions());
    }

    public function testReadStream(): void
    {
        $streamId = EventStreamId::fromString('stream-test');

        $this->store->appendToStream(
            $streamId,
            [
            new EventDescriptor(
                EventId::fromString('event1'),
                EventType::fromString('user_account_registered'),
                new EventData([
                    'username' => 'user_1',
                    'emailAddress' => 'email1@address.com',
                ])
            ),
            new EventDescriptor(
                EventId::fromString('event2'),
                EventType::fromString('user_account_registered'),
                new EventData([
                    'username' => 'user_2',
                    'emailAddress' => 'email2@address.com',
                ])
            ),
            new EventDescriptor(
                EventId::fromString('event3'),
                EventType::fromString('user_account_registered'),
                new EventData([
                    'username' => 'user_3',
                    'emailAddress' => 'email2@address.com',
                ])
            ),
        ],
            AppendStreamOptions::append()
        );

        // Read last event
        $events = $this->store->readStream($streamId, ReadStreamOptions::lastEvent());
        $this->assertCount(1, $events);
        $this->assertEquals('event3', (string) $events->getFirst()->getEventId());
        $lastEventSeqNumber = $events->getFirst()->getSequenceNumber();

        // Read from stream.
        $events = $this->store->readStream($streamId, new ReadStreamOptions());
        $this->assertCount(3, $events);
        $eventsArr = $events->toArray();

        $this->assertEquals('user_1', $eventsArr[0]->getEventData()->getValue('username'));
        $this->assertEquals('user_2', $eventsArr[1]->getEventData()->getValue('username'));
        $this->assertEquals('user_3', $eventsArr[2]->getEventData()->getValue('username'));

        // Read from stream at version
        $events = $this->store->readStream(
            $streamId,
            ReadStreamOptions::read()
            ->forward()
            ->from(1)
        );
        $this->assertCount(1, $events);

        // Read from all.
        $events = $this->store->readStream($this->store->getGlobalStreamId(), new ReadStreamOptions());
        $this->assertCount(3, $events);

        // Read from all at seq number
        $events = $this->store->readStream(
            $this->store->getGlobalStreamId(),
            ReadStreamOptions::read()
            ->forward()
            ->from($lastEventSeqNumber->toInt() - 1)
        );

        $this->assertCount(1, $events);
    }

    public function testGetStream(): void
    {
        $this->assertNull($this->store->getStream(EventStreamId::fromString('does-not-exist')));

        $streamId = EventStreamId::fromString('stream-test');
        $events = [
            new EventDescriptor(
                EventId::fromString('event1'),
                EventType::fromString('user_account_registered'),
                new EventData([
                    'username' => 'user_1',
                    'emailAddress' => 'email1@address.com',
                ])
            ),
            new EventDescriptor(
                EventId::fromString('event1'),
                EventType::fromString('user_account_registered'),
                new EventData([
                    'username' => 'user_1',
                    'emailAddress' => 'email1@address.com',
                ])
            ),
            new EventDescriptor(
                EventId::fromString('event1'),
                EventType::fromString('user_account_registered'),
                new EventData([
                    'username' => 'user_1',
                    'emailAddress' => 'email1@address.com',
                ])
            ),
        ];

        $this->store->appendToStream($streamId, $events, AppendStreamOptions::append());

        $stream = $this->store->getStream($streamId);
        $this->assertNotNull($stream);

        $this->assertEquals(2, $stream->getVersion()->toInt());
    }

    public function testSubscribeToStream(): void
    {
        $streamId = EventStreamId::fromString('stream-test');
        $events = [
            new EventDescriptor(
                EventId::fromString('event1'),
                EventType::fromString('user_account_registered'),
                new EventData([
                    'username' => 'user_1',
                    'emailAddress' => 'email1@address.com',
                ])
            ),
        ];

        $listenedEvents = [];
        $subscriber = new class($listenedEvents) implements EventStoreSubscriberInterface {
            /**
             * @var array
             */
            private $listenedEvents;

            public function __construct(array &$listenedEvents)
            {
                $this->listenedEvents = &$listenedEvents;
            }

            public function onEvent(EventStoreInterface $eventStore, RecordedEventDescriptor $eventDescriptor): void
            {
                $this->listenedEvents[] = $eventDescriptor;
            }

            public function getOptions(): SubscriptionOptions
            {
                return SubscriptionOptions::subscribe()->fromEnd();
            }
        };

        $this->store->subscribeToStream($streamId, $subscriber);

        $this->store->appendToStream($streamId, $events, AppendStreamOptions::append());

        $this->assertCount(1, $listenedEvents);
    }
}
