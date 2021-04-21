<?php

namespace Morebec\Orkestra\EventSourcing\Upcasting;

use Morebec\Orkestra\EventSourcing\EventStore\EventDescriptorParameterTransformer;
use Morebec\Orkestra\EventSourcing\EventStore\EventType;

/**
 * Abstract Implementation of an Upcaster for a specific Event type.
 * It defined the supports method to check that an event is of a specific type.
 */
abstract class AbstractEventSpecificUpcaster implements UpcasterInterface
{
    /**
     * @var EventType
     */
    protected $eventType;

    /**
     * @param EventType|string $eventType
     */
    public function __construct($eventType)
    {
        $this->eventType = EventDescriptorParameterTransformer::stringOrEventType($eventType);
    }

    public function supports(UpcastableEventDescriptor $eventDescriptor): bool
    {
        return $this->eventType === $eventDescriptor->getEventType();
    }
}
