<?php

namespace Tests\Morebec\Orkestra\DateTime;

use InvalidArgumentException;
use Morebec\Orkestra\DateTime\Date;
use Morebec\Orkestra\DateTime\DateRange;
use PHPUnit\Framework\TestCase;

class DateRangeTest extends TestCase
{
    public function testConstruct(): void
    {
        $range = new DateRange(new Date('2020-01-01'), new Date('2020-01-25'));
        self::assertNotNull($range);

        $this->expectException(InvalidArgumentException::class);
        new DateRange(new Date('2021-01-01'), new Date('2020-01-25'));
    }

    public function testIsInRange(): void
    {
        $range = new DateRange(new Date('2020-01-01'), new Date('2020-01-25'));

        self::assertTrue($range->isInRange(new Date('2020-01-05'), true));
        self::assertFalse($range->isInRange(new Date('2020-02-05'), true));
    }

    public function testOverlaps(): void
    {
        $rangeA = new DateRange(new Date('2020-01-01'), new Date('2020-01-25'));
        $rangeB = new DateRange(new Date('2020-01-20'), new Date('2020-02-25'));

        self::assertTrue($rangeA->overlaps($rangeB));

        self::assertFalse($rangeB->overlaps(new DateRange(new Date('2021-01-01'), new Date('2022-01-01'))));
    }
}
