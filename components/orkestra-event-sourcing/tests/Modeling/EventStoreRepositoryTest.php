<?php

namespace Tests\Morebec\Orkestra\EventSourcing\Modeling;

use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\InMemoryEventStore;
use Morebec\Orkestra\EventSourcing\Modeling\EventSourcedAggregateRootVersion;
use Morebec\Orkestra\EventSourcing\Modeling\EventStoreRepository;
use Morebec\Orkestra\EventSourcing\Snapshot\InMemorySnapshotStore;
use Morebec\Orkestra\Messaging\Normalization\ClassMapMessageNormalizer;
use Morebec\Orkestra\Messaging\Normalization\MessageClassMap;
use Morebec\Orkestra\Normalization\ObjectNormalizer;
use PHPUnit\Framework\TestCase;
use Tests\Morebec\Orkestra\EventSourcing\TestEvent;

class EventStoreRepositoryTest extends TestCase
{
    private EventStoreRepository $repository;

    private InMemoryEventStore $eventStore;

    protected function setUp(): void
    {
        $objectNormalizer = new ObjectNormalizer();

        $normalizer = new ClassMapMessageNormalizer(
            new MessageClassMap([
                TestEvent::getTypeName() => TestEvent::class,
            ]),
            $objectNormalizer
        );

        $snapshotStore = new InMemorySnapshotStore();
        $this->eventStore = new InMemoryEventStore(new SystemClock());
        $this->repository = new EventStoreRepository(
            $this->eventStore,
            $normalizer,
            $snapshotStore,
            $objectNormalizer,
            TestAggregate::class,
            'prefix_'
        );
    }

    public function testSave(): void
    {
        $aggregate = new TestAggregate();

        $aggregate->recordDomainEvent(new TestEvent());

        $this->repository->save('test', $aggregate);

        $this->assertEmpty($aggregate->getDomainEvents());

        $stream = $this->eventStore->getStream(EventStreamId::fromString('prefix_test'));
        $this->assertNotNull($stream);
    }

    public function testLoad(): void
    {
        $aggregate = new TestAggregate();

        $aggregate->recordDomainEvent(new TestEvent());

        $this->repository->save('test', $aggregate);

        $loadedAggregate = $this->repository->load('test');

        self::assertEquals($aggregate->testEventReceived, $loadedAggregate->testEventReceived);
        self::assertTrue($loadedAggregate->getVersion()->isEqualTo(EventSourcedAggregateRootVersion::fromInt(0)));
    }
}
