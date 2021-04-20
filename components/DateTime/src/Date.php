<?php

namespace Morebec\Orkestra\DateTime;

use Cake\Chronos\Date as ChronosDate;

/**
 * Date Class implementation based on Chronos Date.
 * This should be considered as the replacement to the native PHP DateTime
 * implementations for Orkestra Projects.
 */
class Date extends ChronosDate
{
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
    public function isBefore(self $date): bool
    {
        return $this->lessThan($date);
    }

    /**
     * Indicates if this date is after a given date.
     *
     * @param Date $date
     */
    public function isAfter(self $date): bool
    {
        return $this->greaterThan($date);
    }

    /**
     * Indicate if this date is between to other dates.
     */
    public function isBetween(self $from, self $to, bool $equals): bool
    {
        return $this->between($from, $to, $equals);
    }
}
