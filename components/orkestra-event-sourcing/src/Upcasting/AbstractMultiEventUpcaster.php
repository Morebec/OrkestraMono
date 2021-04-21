<?php

namespace Morebec\Orkestra\EventSourcing\Upcasting;

/**
 * Implementation of an Upcaster that is specifically designed to allow demultiplexing.
 * It follows an API similar to the {@link AbstractSingleEventUpcaster} by allowing the implementor
 * to implement a `doUpcast` method.
 */
abstract class AbstractMultiEventUpcaster extends AbstractEventSpecificUpcaster implements UpcasterInterface
{
    public function upcast(UpcastableEventDescriptor $eventDescriptor): array
    {
        return $this->doUpcast($eventDescriptor);
    }

    /**
     * Helper method of the AbstractMultiEventUpcaster allowing to return a multiple upcastable events.
     *
     * @return UpcastableEventDescriptor[]
     */
    abstract protected function doUpcast(UpcastableEventDescriptor $eventDescriptor): array;
}
