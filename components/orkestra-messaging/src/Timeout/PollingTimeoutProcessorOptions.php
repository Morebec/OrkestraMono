<?php

namespace Morebec\Orkestra\Messaging\Timeout;

class PollingTimeoutProcessorOptions
{
    public const INFINITE = 0;

    public string $name;

    /**
     *The delay between the polling of the storage in milliseconds.
     */
    public int $pollingDelay = 1000;

    /**
     * Duration in millisecond for the maximum time this processor should be running.
     * A value of 0 means no maximum.
     */
    public int $maxProcessingTime = self::INFINITE;

    public function withName(string $name): self
    {
        if (!$name) {
            throw new \InvalidArgumentException('A Polling Timeout Processor must have a name');
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
