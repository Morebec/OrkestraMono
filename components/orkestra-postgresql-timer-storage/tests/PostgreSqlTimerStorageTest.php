<?php

namespace Tests\Morebec\Orkestra\PostgreSqlTimerStorage;

use Doctrine\DBAL\DriverManager;
use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\Normalization\ClassMapMessageNormalizer;
use Morebec\Orkestra\Messaging\Normalization\MessageClassMap;
use Morebec\Orkestra\Messaging\Timer\TimerWrapper;
use Morebec\Orkestra\Normalization\ObjectNormalizer;
use Morebec\Orkestra\PostgreSqlTimerStorage\PostgreSqlTimerStorage;
use Morebec\Orkestra\PostgreSqlTimerStorage\PostgreSqlTimerStorageConfiguration;
use PHPUnit\Framework\TestCase;

class PostgreSqlTimerStorageTest extends TestCase
{
    /**
     * @var PostgreSqlTimerStorage
     */
    private $store;

    /**
     * @var ClockInterface
     */
    private $clock;

    protected function setUp(): void
    {
        $config = new PostgreSqlTimerStorageConfiguration();

        $messageNormalizer = new ClassMapMessageNormalizer(
            new MessageClassMap([
                TestTimer::getTypeName() => TestTimer::class,
            ]),
            new ObjectNormalizer()
        );

        $this->clock = new SystemClock();
        $connection = DriverManager::getConnection([
            'url' => 'pgsql://postgres@localhost:5432/postgres?charset=UTF8',
        ]);
        $this->store = new PostgreSqlTimerStorage($connection, $config, $messageNormalizer, new ObjectNormalizer());
        $this->store->clear();
    }

    protected function tearDown(): void
    {
        // $this->store->clear();
    }

    public function testAdd(): void
    {
        $timer = new TestTimer('test_add', $this->clock->now()->subDays(1), 'test_value');
        $headers = new MessageHeaders([]);

        $this->store->add(TimerWrapper::wrap($timer, $headers));

        $timers = $this->store->findByEndsAtBefore($this->clock->now());
        $this->assertCount(1, $timers);
    }

    public function testFindByEndsAtBefore(): void
    {
        $timer = new TestTimer('test_find_by_ends_at_before_1', $this->clock->now()->subDays(1), 'test_value');
        $headers = new MessageHeaders([]);

        $this->store->add(TimerWrapper::wrap($timer, $headers));

        $timers = $this->store->findByEndsAtBefore($this->clock->now());
        $this->assertCount(1, $timers);

        $timer = new TestTimer('test_find_by_ends_at_before_2', $this->clock->now()->subDays(1), 'test_value');
        $headers = new MessageHeaders([]);

        $this->store->add(TimerWrapper::wrap($timer, $headers));

        $timers = $this->store->findByEndsAtBefore($this->clock->now()->subDays(5));
        $this->assertEmpty($timers);
    }

    public function testFindByEndsAtBetween(): void
    {
        $timer = new TestTimer('test_find_by_ends_at_between_1', $this->clock->now()->subDays(1), 'test_value');
        $headers = new MessageHeaders([]);

        $this->store->add(TimerWrapper::wrap($timer, $headers));

        $timers = $this->store->findByEndsAtBefore($this->clock->now());
        $this->assertCount(1, $timers);

        $timer = new TestTimer('test_find_by_ends_at_between_2', $this->clock->now()->subDays(1), 'test_value');
        $headers = new MessageHeaders([]);

        $this->store->add(TimerWrapper::wrap($timer, $headers));

        $timers = $this->store->findByEndsAtBefore($this->clock->now()->subDays(5));
        $this->assertEmpty($timers);
    }

    public function testRemove(): void
    {
        $timer = new TestTimer('test_remove', $this->clock->now()->subDays(1), 'test_value');
        $headers = new MessageHeaders([]);

        $this->store->add(TimerWrapper::wrap($timer, $headers));

        $this->store->remove($timer->getId());

        $timers = $this->store->findByEndsAtBefore($this->clock->now());

        $this->assertEmpty($timers);
    }
}
