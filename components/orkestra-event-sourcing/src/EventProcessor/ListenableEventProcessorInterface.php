<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

/**
 * Interface for Event processors that can have listeners attached to them.
 */
interface ListenableEventProcessorInterface extends EventProcessorInterface
{
    /**
     * Adds a listener to this processor.
     */
    public function addListener(EventProcessorListenerInterface $listener): void;

    /**
     * Removes a listener from this processor.
     */
    public function removeListener(EventProcessorListenerInterface $listener): void;
}
