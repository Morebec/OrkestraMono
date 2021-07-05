<?php

namespace Morebec\Orkestra\DateTime;

/**
 * Represents a Range of two dates.
 * This must not be confused with {@link \DateInterval}.
 */
class DateRange
{
    private Date $startDate;

    private Date $endDate;

    public function __construct(Date $startDate, Date $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        if (!$endDate->isAfter($startDate)) {
            throw new \InvalidArgumentException('The end date must be after the start date.');
        }
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
     * Indicates if this date range overlaps another one.
     */
    public function overlaps(self $dateRange): bool
    {
        return ($this->startDate <= $dateRange->endDate) && ($this->endDate >= $dateRange->startDate);
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
