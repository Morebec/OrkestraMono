<?php

namespace Morebec\Orkestra\Messaging\Timer;

use Morebec\Orkestra\Messaging\MessageHeaders;

/**
 * Service responsible for Managing Schedules.
 */
interface TimerManagerInterface
{
    /**
     * Schedules a Message for later processing.
     * It will be stored with its provided headers and executed at a later scheduledAt time.
     * It is also possible to provide a schedulingToken that can be used to cancel the scheduling of message
     * if necessary.
     */
    public function schedule(TimerInterface $timer, ?MessageHeaders $headers = null): void;

    /**
     * Cancels a timer with a given id.
     */
    public function cancel(string $timerId): void;
}
