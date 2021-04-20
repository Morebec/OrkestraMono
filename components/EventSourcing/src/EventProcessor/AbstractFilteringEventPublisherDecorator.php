<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;

/**
 * {@link EventPublisherInterface} Decorator that allows to filter events before sending them
 * to the decorated event publisher.
 */
abstract class AbstractFilteringEventPublisherDecorator implements EventPublisherInterface
{
    /**
     * @var EventPublisherInterface
     */
    private $decorated;

    public function __construct(EventPublisherInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function publishEvent(RecordedEventDescriptor $eventDescriptor): void
    {
        if ($this->filterEvent($eventDescriptor)) {
            $this->decorated->publishEvent($eventDescriptor);
        }
    }

    /**
     * Filters event before sending them out the the decorated {@link EventPublisherInterface}.
     * If this method returns true, the event will be forwarded to the decorated publisher.
     * If this method returns false, the event will be discarded.
     */
    abstract protected function filterEvent(RecordedEventDescriptor $eventDescriptor): bool;
}
