<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

/**
 * Interface for Event Processors Listeners that are specialized to listen to {@link TrackingEventProcessor}.
 */
interface TrackingEventProcessorListenerInterface extends EventProcessorListenerInterface
{
    /**
     * Called when a {@link TrackingEventProcessor} is reset.
     */
    public function onReset(TrackingEventProcessor $processor): void;
}
