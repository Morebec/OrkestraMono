<?php

namespace Morebec\Orkestra\Messaging\Timer;

use Morebec\Orkestra\DateTime\DateTime;

/**
 * Storage of timers.
 */
interface TimerStorageInterface
{
    /**
     * Adds a Wrapped Message to the storage.
     */
    public function add(TimerWrapper $wrapper): void;

    /**
     * Returns timers that have their end date before a given datetime inclusively.
     *
     * @return TimerWrapper[]
     */
    public function findByEndsAtBefore(DateTime $dateTime): array;

    /**
     * Returns Timers that where previously stored
     * and scheduled between a given range of date times (inclusively).
     *
     * @return TimerWrapper[]
     */
    public function findByEndsAtBetween(DateTime $from, DateTime $to): array;

    /**
     * Removes a Timer From this store from this storage.
     */
    public function remove(string $timerId): void;
}
