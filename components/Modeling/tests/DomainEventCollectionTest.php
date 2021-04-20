<?php

namespace Tests\Morebec\Orkestra\Modeling;

use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\Modeling\DomainEventCollection;
use PHPUnit\Framework\TestCase;

class DomainEventCollectionTest extends TestCase
{
    public function testRemove(): void
    {
        $collection = new DomainEventCollection();
        $event = $this->createEventA();
        $collection->add($event);
        $collection->remove($event);

        $this->assertEmpty($collection);
    }

    public function testToArray(): void
    {
        $collection = new DomainEventCollection();

        $event = $this->createEventA();
        $collection->add($event);

        $this->assertEquals([
            $event,
        ], $collection->toArray());
    }

    public function testIsEmpty(): void
    {
        $collection = new DomainEventCollection();
        $this->assertTrue($collection->isEmpty());
        $event = $this->createEventA();
        $collection->add($event);
        $this->assertFalse($collection->isEmpty());
    }

    public function testGetFirst(): void
    {
        $collection = new DomainEventCollection();
        $eventA = $this->createEventA();
        $eventB = $this->createEventB();

        $collection->add($eventA);
        $collection->add($eventB);

        $this->assertEquals($eventA, $collection->getFirst());
    }

    public function testAdd(): void
    {
        $collection = new DomainEventCollection();
        $event = $this->createEventA();
        $collection->add($event);
        $collection->add($event);
        $this->assertCount(2, $collection);
    }

    public function testFilter(): void
    {
        $collection = new DomainEventCollection();
        $event = $this->createEventA();
        $collection->add($event);

        $filteredCollection = $collection->filter(static function (DomainEventInterface $e) use ($event) {
            return $event !== $e;
        });

        $this->assertEmpty($filteredCollection);
    }

    public function testGetLast(): void
    {
        $collection = new DomainEventCollection();
        $eventA = $this->createEventA();
        $eventB = $this->createEventB();

        $collection->add($eventA);
        $collection->add($eventB);

        $this->assertEquals($eventB, $collection->getLast());
    }

    public function testGetLastOfType(): void
    {
        $collection = new DomainEventCollection();

        $collection->add($this->createEventA());
        $collection->add($this->createEventA());
        $lastOfType = $this->createEventA();
        $collection->add($lastOfType);

        $this->assertEquals($lastOfType, $collection->getLastOfType(\get_class($lastOfType)));
    }

    public function testGetFirstOfType(): void
    {
        $collection = new DomainEventCollection();

        $firstOfType = $this->createEventA();
        $collection->add($firstOfType);
        $collection->add($this->createEventA());
        $collection->add($this->createEventA());

        $this->assertEquals($firstOfType, $collection->getFirstOfType(\get_class($firstOfType)));
    }

    public function testClear(): void
    {
        $collection = new DomainEventCollection();
        $collection->add($this->createEventA());
        $collection->add($this->createEventA());
        $collection->clear();
        $this->assertEmpty($collection);
    }

    public function testOfType(): void
    {
        $collection = new DomainEventCollection();
        $collection->add($this->createEventA());
        $collection->add($this->createEventA());
        $collection->add($this->createEventB());

        $this->assertCount(2, $collection->ofType(\get_class($this->createEventA())));
    }

    private function createEventA(): DomainEventInterface
    {
        return new class() implements DomainEventInterface {
            public static function getTypeName(): string
            {
                return 'event_a';
            }
        };
    }

    private function createEventB(): DomainEventInterface
    {
        return new class() implements DomainEventInterface {
            public static function getTypeName(): string
            {
                return 'event_b';
            }
        };
    }
}
