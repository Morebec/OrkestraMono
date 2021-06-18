<?php

namespace Tests\Morebec\Orkestra\DateTime;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\DateTime\FixedClock;
use PHPUnit\Framework\TestCase;

class FixedClockTest extends TestCase
{
    public function testChangeDate(): void
    {
        $fixed = new DateTime('2020/05/10');
        $clock = new FixedClock($fixed);
        $clock->changeDate(new DateTime('2020/01/01'));
        self::assertEquals(new DateTime('2020/01/01'), $clock->now());
    }

    public function testToday(): void
    {
        $fixed = new DateTime('2020/05/10');
        $clock = new FixedClock($fixed);
        self::assertEquals($fixed, $clock->today());
    }

    public function testTomorrow(): void
    {
        $fixed = new DateTime('2020/05/10');
        $clock = new FixedClock($fixed);
        self::assertEquals($fixed->addDay(), $clock->tomorrow());
    }

    public function testNow(): void
    {
        $fixed = new DateTime('2020/05/10');
        $clock = new FixedClock($fixed);
        self::assertEquals($fixed, $clock->now());
    }

    public function testYesterday(): void
    {
        $fixed = new DateTime('2020/05/10');
        $clock = new FixedClock($fixed);
        self::assertEquals($fixed->subDay(), $clock->yesterday());
    }
}
