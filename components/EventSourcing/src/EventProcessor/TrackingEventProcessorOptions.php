<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;

class TrackingEventProcessorOptions
{
    /** @var string Name of this processor. */
    public $name;

    /**
     * Represents the stream that the processor should track.
     *
     * @var EventStreamId
     */
    public $streamId;

    /**
     * Maximum number of events to process whenever it is started.
     * A batch size of 0 means that no batching should be performed.
     *
     * @var int
     */
    public $batchSize = 0;

    /**
     * Indicates if the position of the tracking event processor
     * should be stored in the storage before actually processing the events or after.
     *
     * If stored before this means an at-most-once delivery guarantee. Indeed if the position is saved
     * but the script dies before doing the actual work, on next execution of the processor, it will be considered
     * performed and it won't be executed again.
     *
     * If stored after, this means an at-least-once delivery guarantee. Indeed, if the position cannot be saved
     * after the script has performed the work, it would mean on next execution of the processor, that it would be
     * considered not done, and would be tried again.
     *
     * Depending on the requirements, one might be preferable to the other.
     *
     * For the best possible scenario, if it can be afforded, use a transaction comprising all the events of the batch
     * as well as the position storage.
     *
     * @var bool
     */
    public $storePositionBeforeProcessing = false;

    /**
     * This option indicates if storing the position of the tracking event processor should be done
     * for a batch as a whole (true) or for a single event (false).
     * This can have performance improvements reducing the number of round trips required to the storage,
     * however this means that failures can now potentially happen in batches, which should be
     * considered with the "$storePositionBeforeProcessing" option.
     *
     * For the best possible scenario, if it can be afforded, use a transaction comprising all the events of the batch
     * as well as the position storage.
     *
     * @var bool
     */
    public $storePositionForEachBatch = true;

    public function withStreamId(EventStreamId $streamId): self
    {
        $this->streamId = $streamId;

        return $this;
    }

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withBatchSize(int $nbEvents): self
    {
        $this->batchSize = $nbEvents;

        return $this;
    }

    public function disableBatching(): self
    {
        return $this->withBatchSize(0);
    }

    public function storePositionPerBatch(bool $v = true): self
    {
        $this->storePositionForEachBatch = $v;

        return $this;
    }

    public function storePositionPerEvent(bool $v = true): self
    {
        $this->storePositionForEachBatch = !$v;

        return $this;
    }

    public function storePositionBeforeProcessing(bool $v = true): self
    {
        $this->storePositionBeforeProcessing = $v;

        return $this;
    }

    public function storePositionAfterProcessing(bool $v = true): self
    {
        $this->storePositionBeforeProcessing = !$v;

        return $this;
    }
}
