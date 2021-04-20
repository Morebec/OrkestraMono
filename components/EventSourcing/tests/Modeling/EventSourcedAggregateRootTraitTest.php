<?php

namespace Tests\Morebec\Orkestra\EventSourcing\Modeling;

use Morebec\Orkestra\EventSourcing\Modeling\AbstractEventSourcedAggregateRoot;
use Morebec\Orkestra\EventSourcing\Modeling\EventSourcedAggregateRootTrait;
use PHPUnit\Framework\TestCase;
use Tests\Morebec\Orkestra\EventSourcing\TestEvent;

class EventSourcedAggregateRootTraitTest extends TestCase
{
    public function testOnDomainEvent(): void
    {
        $aggregate = $this->createAggregateRoot();

        $this->assertFalse($aggregate->testEventApplied);
        $aggregate->recordDomainEvent(new TestEvent());
        $this->assertTrue($aggregate->testEventApplied);
    }

    public function createAggregateRoot(): AbstractEventSourcedAggregateRoot
    {
        return new class() extends AbstractEventSourcedAggregateRoot {
            use EventSourcedAggregateRootTrait;

            /** @var bool */
            public $testEventApplied = false;

            public function applyTest(TestEvent $event): void
            {
                $this->testEventApplied = true;
            }
        };
    }
}
