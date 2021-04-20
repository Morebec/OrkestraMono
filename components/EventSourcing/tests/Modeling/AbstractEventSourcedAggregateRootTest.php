<?php

namespace Tests\Morebec\Orkestra\EventSourcing\Modeling;

use Morebec\Orkestra\EventSourcing\Modeling\AbstractEventSourcedAggregateRoot;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\Modeling\DomainEventCollection;
use PHPUnit\Framework\TestCase;

class AbstractEventSourcedAggregateRootTest extends TestCase
{
    public function testRecordDomainEvent(): void
    {
        $aggregate = $this->getAggregateRoot();
        $event = $this->getDomainEvent();

        $aggregate->recordDomainEvent($event);

        $this->assertNotEmpty($aggregate->getDomainEvents());
        $this->assertEquals(1, $aggregate->eventReceived);
    }

    public function testLoadFromHistory(): void
    {
        $aggregate = $this->getAggregateRoot();

        $loadedAggregate = $aggregate::loadFromHistory(new DomainEventCollection([
            $this->getDomainEvent(),
            $this->getDomainEvent(),
        ]));

        $this->assertEmpty($loadedAggregate->getDomainEvents());
    }

    public function getAggregateRoot(): AbstractEventSourcedAggregateRoot
    {
        return new class() extends AbstractEventSourcedAggregateRoot {
            public $eventReceived = 0;

            protected function onDomainEvent(DomainEventInterface $event): void
            {
                $this->eventReceived++;
            }
        };
    }

    public function getDomainEvent(): DomainEventInterface
    {
        return new class() implements DomainEventInterface {
            public static function getTypeName(): string
            {
                return 'test.event';
            }
        };
    }
}
