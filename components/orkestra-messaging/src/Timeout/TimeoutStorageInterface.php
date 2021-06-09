<?php

namespace Morebec\Orkestra\Messaging\Timeout;

use Morebec\Orkestra\DateTime\DateTime;

/**
 * Storage of timeouts.
 */
interface TimeoutStorageInterface
{
    /**
     * Adds a Wrapped Message to the storage.
     */
    public function add(TimeoutWrapper $wrapper): void;

    /**
     * Returns timeouts that have their end date before a given orkestra-datetime inclusively.
     *
     * @return TimeoutWrapper[]
     */
    public function findByEndsAtBefore(DateTime $dateTime): array;

    /**
     * Returns Timeouts that where previously stored
     * and scheduled between a given range of date times (inclusively).
     *
     * @return TimeoutWrapper[]
     */
    public function findByEndsAtBetween(DateTime $from, DateTime $to): array;

    /**
     * Removes a Timeout From this store from this storage.
     */
    public function remove(string $timeoutId): void;
}
