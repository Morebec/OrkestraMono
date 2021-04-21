<?php

namespace Tests\Morebec\Orkestra\DateTime;

use Morebec\Orkestra\DateTime\SystemClock;
use PHPUnit\Framework\TestCase;

class SystemClockTest extends TestCase
{
    public function testToday(): void
    {
        $clock = new SystemClock();
        $this->assertTrue($clock->today()->isToday());
    }

    public function testTomorrow(): void
    {
        $clock = new SystemClock();
        $this->assertTrue($clock->tomorrow()->isTomorrow());
    }

    public function testNow(): void
    {
        $clock = new SystemClock();
        $then = $clock->now();
        $now = $clock->now();

        $this->assertNotEquals($then, $now);
    }

    public function testYesterday(): void
    {
        $clock = new SystemClock();
        $this->assertTrue($clock->yesterday()->isYesterday());
    }
}
