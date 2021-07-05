<?php

namespace Tests\Morebec\Orkestra\DateTime;

use InvalidArgumentException;
use Morebec\Orkestra\DateTime\Date;
use Morebec\Orkestra\DateTime\DateRange;
use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\DateTime\DateTimeRange;
use PHPUnit\Framework\TestCase;

class DateTimeRangeTest extends TestCase
{
    public function testConstruct(): void
    {
        $range = new DateTimeRange(new DateTime('2020-01-01 10:30:00'), new DateTime('2020-01-25 10:30:00'));
        self::assertNotNull($range);

        $this->expectException(InvalidArgumentException::class);
        new DateTimeRange(new DateTime('2021-01-01 10:30:00'), new DateTime('2020-01-25 10:30:00'));
    }

    public function testIsInRange(): void
    {
        $range = new DateTimeRange(new DateTime('2020-01-01 10:30:00'), new DateTime('2020-01-25 10:30:00'));

        self::assertTrue($range->isInRange(new DateTime('2020-01-05 10:30:00'), true));
        self::assertFalse($range->isInRange(new DateTime('2020-02-05 10:30:00'), true));
    }

    public function testOverlaps(): void
    {
        $rangeA = new DateTimeRange(new DateTime('2020-01-01 10:30:00'), new DateTime('2020-01-25 10:30:00'));
        $rangeB = new DateTimeRange(new DateTime('2020-01-20 10:30:00'), new DateTime('2020-02-25 10:30:00'));

        self::assertTrue($rangeA->overlaps($rangeB));

        self::assertFalse($rangeB->overlaps(new DateTimeRange(new DateTime('2021-01-01 10:30:00'), new DateTime('2022-01-01 10:30:00'))));
    }

    public function testToDateRange(): void
    {
        $rangeTime = new DateTimeRange(new DateTime('2020-01-01 10:30:00'), new DateTime('2020-01-25 10:30:00'));

        $expectedRange = new DateRange(new Date('2020-01-01'), new Date('2020-01-25'));

        self::assertEquals($expectedRange, $rangeTime->toDateRange());
    }
}
