<?php

namespace Morebec\Orkestra\EventSourcing\Upcasting;

/**
 * An Upcaster is responsible for modifying an event's normalized data before denormalization
 * so that previously stored data can be made compatible with the current schema of the Typed Event.
 * This is done on the normalized form of an event in order to be able to denormalize it to a specific type,
 * not requiring the code to support all other previous schema versions of a given event.
 */
interface UpcasterInterface
{
    /**
     * Upcasts an event's normalized form to another form and returns it as an array of events.
     * It returns an array of upcasted events to give better control to the upcaster in the processing of events allowing a given
     * event to be upcasted into multiple events (demultiplexing) or entirely skipped. The events should always
     * be in their normalized form, to allow deferring denormalization to a later stage.
     *
     * @param UpcastableEventDescriptor $eventDescriptor the data
     *
     * @return UpcastableEventDescriptor[] zero, one or multiple upcasted normalized events
     */
    public function upcast(UpcastableEventDescriptor $eventDescriptor): array;

    /**
     * Indicates if this Upcaster supports a given event and data schema.
     */
    public function supports(UpcastableEventDescriptor $eventDescriptor): bool;
}
