<?php

namespace Tests\Morebec\Orkestra\DateTime;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\DateTime\FixedClock;
use PHPUnit\Framework\TestCase;

class FixedClockTest extends TestCase
{
    public function testToday(): void
    {
        $fixed = new DateTime('2020/05/10');
        $clock = new FixedClock($fixed);
        $this->assertEquals($fixed, $clock->today());
    }

    public function testTomorrow(): void
    {
        $fixed = new DateTime('2020/05/10');
        $clock = new FixedClock($fixed);
        $this->assertEquals($fixed->addDay(), $clock->tomorrow());
    }

    public function testNow(): void
    {
        $fixed = new DateTime('2020/05/10');
        $clock = new FixedClock($fixed);
        $this->assertEquals($fixed, $clock->now());
    }

    public function testYesterday(): void
    {
        $fixed = new DateTime('2020/05/10');
        $clock = new FixedClock($fixed);
        $this->assertEquals($fixed->subDay(), $clock->yesterday());
    }
}
