<?php

namespace Tests\Morebec\Orkestra\Messaging\Timer;

use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\Messaging\Timer\PollingTimerProcessor;
use Morebec\Orkestra\Messaging\Timer\PollingTimerProcessorOptions;
use Morebec\Orkestra\Messaging\Timer\TimerPublisherInterface;
use Morebec\Orkestra\Messaging\Timer\TimerStorageInterface;
use PHPUnit\Framework\TestCase;

class PollingTimerProcessorTest extends TestCase
{
    public function testStart(): void
    {
        $storage = $this->getMockBuilder(TimerStorageInterface::class)->getMock();
        $publisher = $this->getMockBuilder(TimerPublisherInterface::class)->getMock();
        $options = (new PollingTimerProcessorOptions())
            ->withName('test_processor')
            ->withDelay(0)
            ->withMaximumProcessingTime(50)
        ;

        $processor = new PollingTimerProcessor(new SystemClock(), $publisher, $storage, $options);

        $processor->start();

        // It should stop after 50 ms.
        $this->expectNotToPerformAssertions();
    }

    public function testIsRunning(): void
    {
        $storage = $this->getMockBuilder(TimerStorageInterface::class)->getMock();
        $publisher = $this->getMockBuilder(TimerPublisherInterface::class)->getMock();
        $options = (new PollingTimerProcessorOptions())
            ->withName('test_processor')
            ->withDelay(0)
            ->withMaximumProcessingTime(50)
        ;

        $processor = new PollingTimerProcessor(new SystemClock(), $publisher, $storage, $options);

        $this->assertFalse($processor->isRunning());
        $processor->start();
        $this->assertFalse($processor->isRunning());
    }

    public function testGetProcessingTime(): void
    {
        $storage = $this->getMockBuilder(TimerStorageInterface::class)->getMock();
        $publisher = $this->getMockBuilder(TimerPublisherInterface::class)->getMock();
        $options = (new PollingTimerProcessorOptions())
            ->withName('test_processor')
            ->withDelay(0)
            ->withMaximumProcessingTime(50)
        ;

        $processor = new PollingTimerProcessor(new SystemClock(), $publisher, $storage, $options);
        $this->assertEquals(0, $processor->getProcessingTime());

        $processor->start();

        $this->assertGreaterThanOrEqual(50, $processor->getProcessingTime());
    }
}
