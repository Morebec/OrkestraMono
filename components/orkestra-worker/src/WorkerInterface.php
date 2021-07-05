<?php

namespace Morebec\Orkestra\Worker;

/**
 * Represents a worker that should perform a given task a a background process.
 */
interface WorkerInterface
{
    /**
     * Starts this worker to perform its work.
     *
     * @throws WorkerExceptionInterface
     */
    public function start(): void;

    /**
     * Stops this worker.
     *
     * @throws WorkerExceptionInterface
     */
    public function stop(): void;

    /**
     * Indicates if this worker is running.
     */
    public function isRunning(): bool;
}
