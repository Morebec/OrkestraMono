<?php

namespace Morebec\Orkestra\Messaging\Timeout;

use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;

/**
 * Default Implementation of a {@link TimeoutManagerInterface}.
 */
class TimeoutManager implements TimeoutManagerInterface
{
    /**
     * @var TimeoutStorageInterface
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
        TimeoutStorageInterface $storage,
        MessageBusInterface $messageBus
    ) {
        $this->storage = $storage;
        $this->clock = $clock;
        $this->messageBus = $messageBus;
    }

    public function schedule(TimeoutInterface $timeout, ?MessageHeaders $headers = null): void
    {
        if (!$headers) {
            $headers = new MessageHeaders();
        }

        $this->storage->add(TimeoutWrapper::wrap($timeout, $headers));
    }

    public function cancel(string $timeoutId): void
    {
        $this->storage->remove($timeoutId);
    }
}
