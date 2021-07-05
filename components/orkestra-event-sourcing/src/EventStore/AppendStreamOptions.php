<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

class AppendStreamOptions
{
    /**
     * Represents the version at which stream is expected to be at, in order to perform an optimistic concurrency check.
     * Set to null to disable concurrency check.
     */
    public ?EventStreamVersion $expectedStreamVersion = null;

    /**
     * Indicates if the append operation should be performed in a transaction if the underlying implementation
     * of the event store supports it.
     */
    public bool $transactional = false;

    public static function append(): self
    {
        return (new self())
            ->disableOptimisticConcurrencyCheck()
        ;
    }

    public function expectVersion(?EventStreamVersion $version): self
    {
        $this->expectedStreamVersion = $version;

        return $this;
    }

    public function disableOptimisticConcurrencyCheck(): self
    {
        $this->expectedStreamVersion = null;

        return $this;
    }

    public function transactional(bool $transactional = true): self
    {
        $this->transactional = $transactional;

        return $this;
    }
}
