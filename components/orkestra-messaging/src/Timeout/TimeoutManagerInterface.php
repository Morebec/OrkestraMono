<?php

namespace Morebec\Orkestra\Messaging\Timeout;

use Morebec\Orkestra\Messaging\MessageHeaders;

/**
 * Service responsible for Managing Schedules.
 */
interface TimeoutManagerInterface
{
    /**
     * Schedules a Message for later processing.
     * It will be stored with its provided headers and executed at a later scheduledAt time.
     * It is also possible to provide a schedulingToken that can be used to cancel the scheduling of message
     * if necessary.
     */
    public function schedule(TimeoutInterface $timeout, ?MessageHeaders $headers = null): void;

    /**
     * Cancels a timeout with a given id.
     */
    public function cancel(string $timeoutId): void;
}
