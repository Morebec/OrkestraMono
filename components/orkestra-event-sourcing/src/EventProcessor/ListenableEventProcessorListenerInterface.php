<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;

/**
 * Interface for listeners of event processors that are listenable.
 */
interface ListenableEventProcessorListenerInterface
{
    /**
     * Called whenever a call to the start method of a processor is made.
     * This hook is called right before any actual work is performed.
     */
    public function onStart(TrackingEventProcessor $processor): void;

    /**
     * Called whenever a call to the stop method of a processor.
     */
    public function onStop(TrackingEventProcessor $processor): void;

    /**
     * This hook is called right before the processors actively works on an event.
     */
    public function beforeEvent(TrackingEventProcessor $processor, RecordedEventDescriptor $eventDescriptor): void;

    /**
     * This hook is called after an event has been processed and marked as such in the {@link EventStorePositionStorageInterface}.
     */
    public function afterEvent(TrackingEventProcessor $processor, RecordedEventDescriptor $eventDescriptor): void;
}
