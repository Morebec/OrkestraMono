<?php

namespace Morebec\Orkestra\DateTime;

/**
 * Represents a Range of two date times.
 * This must not be confused with {@link \DateInterval}.
 */
class DateTimeRange
{
    /**
     * @var DateTime
     */
    private $startDate;
    /**
     * @var DateTime
     */
    private $endDate;

    public function __construct(DateTime $startDate, DateTime $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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
