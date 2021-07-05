<?php

namespace Morebec\Orkestra\Worker;

/**
 * Contract for workers that can be listened to.
 */
interface ListenableWorkerInterface extends WorkerInterface
{
    /**
     * Adds a listener to this worker.
     */
    public function addListener(WorkerListenerInterface $listener): void;

    /**
     * Removes a listener form this worker.
     */
    public function removeListener(WorkerListenerInterface $listener): void;
}
