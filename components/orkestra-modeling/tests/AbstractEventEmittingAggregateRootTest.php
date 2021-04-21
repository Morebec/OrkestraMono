<?php

namespace Tests\Morebec\Orkestra\Modeling;

use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\Modeling\AbstractEventEmittingAggregateRoot;
use PHPUnit\Framework\TestCase;

class AbstractEventEmittingAggregateRootTest extends TestCase
{
    public function testRecordDomainEvent(): void
    {
        $ar = $this->createAggregateRoot();
        $ar->recordDomainEvent($this->createEvent());

        $this->assertNotEmpty($ar->getDomainEvents());
    }

    public function testGetDomainEvents(): void
    {
        $ar = $this->createAggregateRoot();
        $ar->recordDomainEvent($this->createEvent());

        $collection = $ar->getDomainEvents();
        $this->assertNotEmpty($collection);

        // The Get Domain Events should return a copy of the domain events as privately handled by the aggregate root.
        $collection->add($this->createEvent());
        $this->assertNotEquals($ar->getDomainEvents(), $collection);
    }

    public function testClearDomainEvents(): void
    {
        $ar = $this->createAggregateRoot();
        $ar->recordDomainEvent($this->createEvent());

        $ar->clearDomainEvents();

        $collection = $ar->getDomainEvents();
        $this->assertEmpty($collection);
    }

    private function createAggregateRoot(): AbstractEventEmittingAggregateRoot
    {
        return new class() extends AbstractEventEmittingAggregateRoot {
        };
    }

    private function createEvent(): DomainEventInterface
    {
        return new class() implements DomainEventInterface {
            public static function getTypeName(): string
            {
                return 'event';
            }
        };
    }
}
