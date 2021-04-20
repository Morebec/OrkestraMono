<?php

namespace Morebec\Orkestra\DateTime;

use Cake\Chronos\Chronos;

/**
 * Date Class implementation based on Chronos Date.
 * This should be considered as the replacement to the native PHP DateTime
 * implementations.
 */
class DateTime extends Chronos
{
    public const DEFAULT_SYSTEM_TIME_ZONE = 'UTC';

    /**
     * Returns a timestamp of number of seconds since epoch with a millisecond precision.
     * E.g.: 988644579.9930.
     */
    public function getMillisTimestamp(): float
    {
        return (float) $this->format('U.u');
    }

    /**
     * Indicates if this date is before another one.
     */
    public function isBefore(self $dateTime): bool
    {
        return $this->lessThan($dateTime);
    }

    /**
     * Indicates if this date is after a given date.
     */
    public function isAfter(self $dateTime): bool
    {
        return $this->greaterThan($dateTime);
    }

    /**
     * Indicate if this date is between to other dates.
     *
     * @param DateTime $from
     * @param DateTime $to
     * @param bool     $equals Indicates if a > and < comparison should be used or <= or >=
     */
    public function isBetween(self $from, self $to, bool $equals = true): bool
    {
        return $this->between($from, $to, $equals);
    }
}
