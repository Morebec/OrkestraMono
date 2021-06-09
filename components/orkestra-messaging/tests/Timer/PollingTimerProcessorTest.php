<?php

namespace Tests\Morebec\Orkestra\Messaging\Timeout;

use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\Messaging\Timeout\PollingTimeoutProcessor;
use Morebec\Orkestra\Messaging\Timeout\PollingTimeoutProcessorOptions;
use Morebec\Orkestra\Messaging\Timeout\TimeoutPublisherInterface;
use Morebec\Orkestra\Messaging\Timeout\TimeoutStorageInterface;
use PHPUnit\Framework\TestCase;

class PollingTimerProcessorTest extends TestCase
{
    public function testStart(): void
    {
        $storage = $this->getMockBuilder(TimeoutStorageInterface::class)->getMock();
        $publisher = $this->getMockBuilder(TimeoutPublisherInterface::class)->getMock();
        $options = (new PollingTimeoutProcessorOptions())
            ->withName('test_processor')
            ->withDelay(0)
            ->withMaximumProcessingTime(50)
        ;

        $processor = new PollingTimeoutProcessor(new SystemClock(), $publisher, $storage, $options);

        $processor->start();

        // It should stop after 50 ms.
        $this->expectNotToPerformAssertions();
    }

    public function testIsRunning(): void
    {
        $storage = $this->getMockBuilder(TimeoutStorageInterface::class)->getMock();
        $publisher = $this->getMockBuilder(TimeoutPublisherInterface::class)->getMock();
        $options = (new PollingTimeoutProcessorOptions())
            ->withName('test_processor')
            ->withDelay(0)
            ->withMaximumProcessingTime(50)
        ;

        $processor = new PollingTimeoutProcessor(new SystemClock(), $publisher, $storage, $options);

        $this->assertFalse($processor->isRunning());
        $processor->start();
        $this->assertFalse($processor->isRunning());
    }

    public function testGetProcessingTime(): void
    {
        $storage = $this->getMockBuilder(TimeoutStorageInterface::class)->getMock();
        $publisher = $this->getMockBuilder(TimeoutPublisherInterface::class)->getMock();
        $options = (new PollingTimeoutProcessorOptions())
            ->withName('test_processor')
            ->withDelay(0)
            ->withMaximumProcessingTime(50)
        ;

        $processor = new PollingTimeoutProcessor(new SystemClock(), $publisher, $storage, $options);
        $this->assertEquals(0, $processor->getProcessingTime());

        $processor->start();

        $this->assertGreaterThanOrEqual(50, $processor->getProcessingTime());
    }
}
