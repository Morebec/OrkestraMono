<?php

namespace Morebec\Orkestra\EventSourcing\Upcasting;

/**
 * Implementation of an upcaster that does a one-to-one mapping between a specific event at version x and version x+1.
 */
abstract class AbstractSingleEventUpcaster extends AbstractEventSpecificUpcaster implements UpcasterInterface
{
    public function upcast(UpcastableEventDescriptor $eventDescriptor): array
    {
        return [$this->doUpcast($eventDescriptor)];
    }

    /**
     * Helper method of the single event Upcaster allowing to return a single event, instead of an array.
     */
    abstract protected function doUpcast(UpcastableEventDescriptor $event): UpcastableEventDescriptor;
}
