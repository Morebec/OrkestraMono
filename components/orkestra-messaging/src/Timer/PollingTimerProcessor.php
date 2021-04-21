<?php

namespace Morebec\Orkestra\Messaging\Timer;

use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\DateTime\DateTime;

/**
 * Implementation of a TimerProcessor that continuously polls the storage for timers to be processed.
 * This processor always ends up removing the timer from the storage once it is published.
 * It does not check whether the publishing effort was successful or not. This responsibility is delegated to the publisher.
 */
class PollingTimerProcessor implements TimerProcessorInterface
{
    /**
     * @var TimerPublisherInterface
     */
    private $publisher;

    /**
     * @var PollingTimerProcessorOptions
     */
    private $options;

    /**
     * Indicates if this processor is currently running or not.
     *
     * @var bool
     */
    private $running;

    /**
     * @var TimerStorageInterface
     */
    private $storage;

    /**
     * @var ClockInterface
     */
    private $clock;

    /**
     * @var DateTime|null
     */
    private $startedAt;

    public function __construct(
        ClockInterface $clock,
        TimerPublisherInterface $publisher,
        TimerStorageInterface $storage,
        PollingTimerProcessorOptions $options
    ) {
        $this->publisher = $publisher;
        $this->options = $options;
        $this->storage = $storage;
        $this->clock = $clock;
        $this->running = false;
    }

    public function getName(): string
    {
        return $this->options->name;
    }

    public function start(): void
    {
        $this->running = true;
        $this->startedAt = $this->clock->now();

        do {
            $now = $this->clock->now();
            $wrappers = $this->storage->findByEndsAtBefore($now);

            foreach ($wrappers as $wrapper) {
                $timer = $wrapper->getTimer();
                $this->publisher->publish($timer, $wrapper->getMessageHeaders());
                $this->storage->remove($timer->getId());
            }

            usleep($this->options->pollingDelay);
        } while ($this->mustContinue());

        $this->stop();
    }

    public function stop(): void
    {
        $this->running = false;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Returns the processing time of this processor in milliseconds.
     */
    public function getProcessingTime(): float
    {
        if (!$this->startedAt) {
            return 0;
        }

        $now = $this->clock->now()->getMillisTimestamp();
        $startAt = $this->startedAt->getMillisTimestamp();

        return ($now - $startAt) * 1000;
    }

    protected function mustContinue(): bool
    {
        if (!$this->isRunning()) {
            return false;
        }

        $processingTime = $this->getProcessingTime();

        if ($this->options->maxProcessingTime !== 0) {
            return $processingTime <= $this->options->maxProcessingTime;
        }

        return $this->isRunning();
    }
}
