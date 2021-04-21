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
    /**
     * @var EventStoreRepository
     */
    private $repository;
    private $eventStore;

    protected function setUp(): void
    {
        $objectNormalizer = new ObjectNormalizer();

        $normalizer = new ClassMapMessageNormalizer(
            new MessageClassMap([
                TestEvent::getTypeName() => TestEvent::class,
            ]),
            $objectNormalizer
        );

        $snapshotRepository = new InMemorySnapshotStore();
        $this->eventStore = new InMemoryEventStore(new SystemClock());
        $this->repository = new EventStoreRepository($this->eventStore, $normalizer, $snapshotRepository, $objectNormalizer, TestAggregate::class, 'prefix_');
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

        $this->assertEquals($aggregate->testEventReceived, $loadedAggregate->testEventReceived);
        $this->assertTrue($loadedAggregate->getVersion()->isEqualTo(EventSourcedAggregateRootVersion::fromInt(0)));
    }
}
