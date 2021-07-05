<?php

namespace Morebec\Orkestra\Messaging\Timeout;

use Morebec\Orkestra\Messaging\MessageHeaders;

/**
 * Default Implementation of a {@link TimeoutManagerInterface}.
 */
class TimeoutManager implements TimeoutManagerInterface
{
    private TimeoutStorageInterface $storage;

    public function __construct(TimeoutStorageInterface $storage)
    {
        $this->storage = $storage;
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
