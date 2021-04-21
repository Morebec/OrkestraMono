<?php

namespace Tests\Morebec\Orkestra\PostgreSqlEventStore;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\EventSourcing\EventStore\AppendStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\ConcurrencyException;
use Morebec\Orkestra\EventSourcing\EventStore\EventData;
use Morebec\Orkestra\EventSourcing\EventStore\EventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\EventId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreSubscriberInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamNotFoundException;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\EventStore\EventType;
use Morebec\Orkestra\EventSourcing\EventStore\ReadStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\SubscriptionOptions;
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventStore;
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventStoreConfiguration;
use PHPUnit\Framework\TestCase;

class PostgreSqlEventStoreTest extends TestCase
{
    /**
     * @var PostgreSqlEventStore
     */
    private $store;

    /**
     * @var ClockInterface
     */
    private $clock;

    protected function setUp(): void
    {
        $config = new PostgreSqlEventStoreConfiguration();

        $this->clock = new SystemClock();

        $connection = DriverManager::getConnection([
            'url' => 'pgsql://postgres@localhost:5432/postgres?charset=UTF8',
        ], new Configuration());
        $this->store = new PostgreSqlEventStore($connection, $config, $this->clock);
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

    public function testAppendStream(): void
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

        $stream = $this->store->getStream($streamId);
        $this->assertEquals(EventStreamVersion::fromInt(2), $stream->getVersion());

        // Expect concurrency issue
        $this->expectException(ConcurrencyException::class);
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
            AppendStreamOptions::append()->expectVersion(EventStreamVersion::initial())
        );
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

    public function testSubscribeToStream(): void
    {
        $streamId = EventStreamId::fromString('stream-test');

        // Adding a past event.
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
        ],
            AppendStreamOptions::append()
        );
        $this->store->notifySubscribers();

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

        // Note: Postgres will keep the events in the channel until they are consumed.
        // However, subscribers should not be notified of past events, only new ones, therefore the following
        // call should not trigger a call to a subscriber.
        $this->store->notifySubscribers();
        $this->assertCount(0, $listenedEvents);

        // However this one should
        $this->store->appendToStream(
            $streamId,
            [
            new EventDescriptor(
                EventId::fromString('event2'),
                EventType::fromString('user_account_registered'),
                new EventData([
                    'username' => 'user_1',
                    'emailAddress' => 'email1@address.com',
                ])
            ),
        ],
            AppendStreamOptions::append()
        );

        $this->store->notifySubscribers();
        $this->assertCount(1, $listenedEvents);
    }
}
