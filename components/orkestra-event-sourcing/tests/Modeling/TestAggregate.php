<?php

namespace Tests\Morebec\Orkestra\EventSourcing\Modeling;

use Morebec\Orkestra\EventSourcing\Modeling\AbstractEventSourcedAggregateRoot;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Tests\Morebec\Orkestra\EventSourcing\TestEvent;

class TestAggregate extends AbstractEventSourcedAggregateRoot
{
    /**
     * @var bool
     */
    public $testEventReceived = false;

    protected function onDomainEvent(DomainEventInterface $event): void
    {
        if ($event instanceof TestEvent) {
            $this->testEventReceived = true;
        }
    }
}
