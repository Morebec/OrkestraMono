<?php

namespace Morebec\Orkestra\DateTime;

/**
 * Represents a Range of two date times.
 * This must not be confused with {@link \DateInterval}.
 */
class DateTimeRange
{
    private DateTime $startDate;

    private DateTime $endDate;

    public function __construct(DateTime $startDate, DateTime $endDate)
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
    public function isInRange(DateTime $dateTime, bool $includeEnd = true): bool
    {
        return $dateTime->isBetween($this->startDate, $this->endDate, $includeEnd);
    }

    /**
     * Indicates if this date range overlaps another one.
     */
    public function overlaps(self $dateRange): bool
    {
        return ($this->startDate <= $dateRange->endDate) && ($this->endDate >= $dateRange->startDate);
    }

    /**
     * Lossy function to convert this datetime range into a date range.
     */
    public function toDateRange(): DateRange
    {
        return new DateRange(new Date($this->startDate), new Date($this->endDate));
    }

    /**
     * Returns the interval between the start and end date.
     */
    public function getInterval(bool $absolute = false): \DateInterval
    {
        return $this->endDate->diff($this->startDate, $absolute);
    }

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }
}
