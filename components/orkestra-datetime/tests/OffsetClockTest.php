<?php

namespace Tests\Morebec\Orkestra\DateTime;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\DateTime\OffsetClock;
use PHPUnit\Framework\TestCase;

class OffsetClockTest extends TestCase
{
    public function testToday(): void
    {
        $fixed = new DateTime('2020/05/10');
        $clock = new OffsetClock($fixed);
        $this->assertEquals($fixed, $clock->today());
    }

    public function testTomorrow(): void
    {
        $offset = new DateTime('2020/05/10');
        $clock = new OffsetClock($offset);
        $this->assertEquals($offset->addDay(), $clock->tomorrow());
    }

    public function testNow(): void
    {
        $offset = new DateTime('2020/05/10');
        $clock = new OffsetClock($offset);
        sleep(1);
        $this->assertEquals($offset->addSecond()->getTimestamp(), $clock->now()->getTimestamp());
    }

    public function testYesterday(): void
    {
        $offset = new DateTime('2020/05/10');
        $clock = new OffsetClock($offset);
        $this->assertEquals($offset->subDay(), $clock->yesterday());
    }
}
