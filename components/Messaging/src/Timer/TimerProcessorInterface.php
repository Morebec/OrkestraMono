<?php

namespace Morebec\Orkestra\Messaging\Timer;

/**
 * A Timer processor is responsible for finding timers that need to be processed based on the current time and
 * the end time of a timer.
 */
interface TimerProcessorInterface
{
    /**
     * Returns the name of this timer processor.
     * This name should always be unique to avoid collisions between multiple timer processor running at the same
     * time and sharing resources.
     */
    public function getName(): string;

    /**
     * Starts this timer processor so it can do its work on timers.
     */
    public function start(): void;

    /**
     * Shuts down this timer processor gracefully.
     */
    public function stop(): void;

    /**
     * Indicates if this timer processor is running.
     */
    public function isRunning(): bool;
}
