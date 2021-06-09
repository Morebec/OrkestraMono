<?php

namespace Morebec\Orkestra\Messaging\Timeout;

/**
 * A Timeout processor is responsible for finding timeouts that need to be processed based on the current time and
 * the end time of a timeout.
 */
interface TimeoutProcessorInterface
{
    /**
     * Returns the name of this timeout processor.
     * This name should always be unique to avoid collisions between multiple timeout processor running at the same
     * time and sharing resources.
     */
    public function getName(): string;

    /**
     * Starts this timeout processor so it can do its work on timeouts.
     */
    public function start(): void;

    /**
     * Shuts down this timeout processor gracefully.
     */
    public function stop(): void;

    /**
     * Indicates if this timeout processor is running.
     */
    public function isRunning(): bool;
}
