<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

class AppendStreamOptions
{
    /**
     * Represents the version at which stream is expected to be at, in order to perform an optimistic concurrency check.
     * Set to null to disable concurrency check.
     *
     * @var ?EventStreamVersion
     */
    public $expectedStreamVersion = null;

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
}
