<?php

namespace Morebec\Orkestra\Worker;

/**
 * Time that performs work at a specified interval.
 */
abstract class AbstractTimedWorker implements WorkerInterface
{
    protected bool $running;

    /**
     * Wait interval between executions of the {@link self::doWork()} method in milliseconds.
     */
    protected int $executionInterval;

    /**
     * Number of times the do work method was fully executed.
     */
    protected int $executionCount;

    public function __construct(int $executionInterval)
    {
        $this->running = false;
        if ($executionInterval <= 0) {
            throw new \InvalidArgumentException('The execution interval must be a positive number');
        }

        $this->executionInterval = $executionInterval;
        $this->executionCount = 0;
    }

    public function start(): void
    {
        $this->running = true;
        while ($this->isRunning()) {
            $this->doWork();
            $this->executionCount++;
            if ($this->isRunning()) {
                usleep(1000 * $this->executionInterval);
            }
        }
    }

    /**
     * Performs work.
     */
    abstract public function doWork(): void;

    public function stop(): void
    {
        $this->running = false;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }
}
