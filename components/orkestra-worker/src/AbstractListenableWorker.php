<?php

namespace Morebec\Orkestra\Worker;

abstract class AbstractListenableWorker implements ListenableWorkerInterface
{
    /**
     * @var WorkerListenerInterface[]
     */
    protected array $listeners;

    public function __construct(iterable $listeners = [])
    {
        $this->listeners = [];
        foreach ($listeners as $listener) {
            $this->addListener($listener);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addListener(WorkerListenerInterface $listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * {@inheritDoc}
     */
    public function removeListener(WorkerListenerInterface $listener): void
    {
        $this->listeners = array_filter($this->listeners, static fn (WorkerListenerInterface $l) => $listener !== $l);
    }

    /**
     * {@inheritDoc}
     */
    public function start(): void
    {
        $this->doStart();
        foreach ($this->listeners as $listener) {
            $listener->onStarted();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function stop(): void
    {
        $this->doStop();
        if ($this->isRunning()) {
            return;
        }

        foreach ($this->listeners as $listener) {
            $listener->onStopped();
        }
    }

    abstract protected function doStart(): void;

    abstract protected function doStop(): void;
}
