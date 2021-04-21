<?php

namespace Morebec\Orkestra\Messaging\Timer;

use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;

/**
 * Default Implementation of a {@link TimerManagerInterface}.
 */
class TimerManager implements TimerManagerInterface
{
    /**
     * @var TimerStorageInterface
     */
    private $storage;

    /**
     * @var ClockInterface
     */
    private $clock;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(
        ClockInterface $clock,
        TimerStorageInterface $storage,
        MessageBusInterface $messageBus
    ) {
        $this->storage = $storage;
        $this->clock = $clock;
        $this->messageBus = $messageBus;
    }

    public function schedule(TimerInterface $timer, ?MessageHeaders $headers = null): void
    {
        if (!$headers) {
            $headers = new MessageHeaders();
        }

        $this->storage->add(TimerWrapper::wrap($timer, $headers));
    }

    public function cancel(string $timerId): void
    {
        $this->storage->remove($timerId);
    }
}
