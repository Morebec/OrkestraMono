<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

/**
 * Interface for event processors that support replaying capabilities.
 * This can be useful for some side-effect event processors and mostly for projection processor.
 */
interface ReplayableEventProcessorInterface extends EventProcessorInterface
{
    /**
     * Allows to replay this event processor, from a given position.
     * If position is null it will be replayed from the start.
     */
    public function replay(int $position = null): void;
}
