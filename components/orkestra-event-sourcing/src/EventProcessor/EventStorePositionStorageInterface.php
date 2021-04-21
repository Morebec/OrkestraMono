<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

/**
 * Interface representing the storage used to track the progress of event processors or any other service
 * keeping a position of the event store. {@link EventStorePositionStorageInterface}.
 */
interface EventStorePositionStorageInterface
{
    /**
     * Sets the last processed event position from an event queue or event store
     * for a given processor with ID.
     */
    public function set(string $processorId, ?int $position): void;

    /**
     * Resets the last processed event position for a given processor with ID.
     */
    public function reset(string $processorId): void;

    /**
     * Returns the last processed event position from an event queue or event store
     * for a given processor with ID.
     */
    public function get(string $processorId): ?int;
}
