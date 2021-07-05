<?php

namespace Morebec\Orkestra\Worker;

/**
 * Service capable of listening to a worker.
 */
interface WorkerListenerInterface
{
    /**
     * Called when a worker is started.
     */
    public function onStarted(): void;

    /**
     * Called when a worker was stopped.
     */
    public function onStopped(): void;
}
