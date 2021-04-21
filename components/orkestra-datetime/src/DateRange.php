<?php

namespace Morebec\Orkestra\DateTime;

/**
 * Represents a Range of two dates.
 * This must not be confused with {@link \DateInterval}.
 */
class DateRange
{
    /**
     * @var Date
     */
    private $startDate;

    /**
     * @var Date
     */
    private $endDate;

    public function __construct(Date $startDate, Date $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Indicates if a given date time is in the range of dates.
     *
     * @param bool $includeEnd Indicates if a > and < comparison should be used or <= or >=
     */
    public function isInRange(Date $date, bool $includeEnd = true): bool
    {
        return $date->isBetween($this->startDate, $this->endDate, $includeEnd);
    }

    /**
     * Returns the interval between the start and end date.
     */
    public function getInterval(bool $absolute = false): \DateInterval
    {
        return $this->endDate->diff($this->startDate, $absolute);
    }

    public function getStartDate(): Date
    {
        return $this->startDate;
    }

    public function getEndDate(): Date
    {
        return $this->endDate;
    }
}
