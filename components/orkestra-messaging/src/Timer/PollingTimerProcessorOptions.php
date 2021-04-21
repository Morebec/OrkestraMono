<?php

namespace Morebec\Orkestra\Messaging\Timer;

class PollingTimerProcessorOptions
{
    public const INFINITE = 0;

    /** @var string */
    public $name;

    /**
     *The delay between the polling of the storage in milliseconds.
     *
     * @var int
     */
    public $pollingDelay = 1000;

    /**
     * Duration in millisecond for the maximum time this processor should be running.
     * A value of 0 means no maximum.
     *
     * @var int
     */
    public $maxProcessingTime = self::INFINITE;

    public function withName(string $name): self
    {
        if (!$name) {
            throw new \InvalidArgumentException('A Polling Timer Processor must have a name');
        }

        $this->name = $name;

        return $this;
    }

    public function withDelay(int $delay): self
    {
        if ($delay < 0) {
            throw new \InvalidArgumentException('A Polling delay must be a positive integer.');
        }
        $this->pollingDelay = $delay;

        return $this;
    }

    public function withMaximumProcessingTime(int $duration): self
    {
        $this->maxProcessingTime = $duration;

        return $this;
    }
}
