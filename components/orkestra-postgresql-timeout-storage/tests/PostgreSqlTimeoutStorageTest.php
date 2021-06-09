<?php

namespace Tests\Morebec\Orkestra\PostgreSqlTimeoutStorage;

use Doctrine\DBAL\DriverManager;
use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\Normalization\ClassMapMessageNormalizer;
use Morebec\Orkestra\Messaging\Normalization\MessageClassMap;
use Morebec\Orkestra\Messaging\Timeout\TimeoutWrapper;
use Morebec\Orkestra\Normalization\ObjectNormalizer;
use Morebec\Orkestra\PostgreSqlTimeoutStorage\PostgreSqlTimeoutStorage;
use Morebec\Orkestra\PostgreSqlTimeoutStorage\PostgreSqlTimeoutStorageConfiguration;
use PHPUnit\Framework\TestCase;

class PostgreSqlTimeoutStorageTest extends TestCase
{
    /**
     * @var PostgreSqlTimeoutStorage
     */
    private $store;

    /**
     * @var ClockInterface
     */
    private $clock;

    protected function setUp(): void
    {
        $config = new PostgreSqlTimeoutStorageConfiguration();

        $messageNormalizer = new ClassMapMessageNormalizer(
            new MessageClassMap([
                TestTimeout::getTypeName() => TestTimeout::class,
            ]),
            new ObjectNormalizer()
        );

        $this->clock = new SystemClock();
        $connection = DriverManager::getConnection([
            'url' => 'pgsql://postgres@localhost:5432/postgres?charset=UTF8',
        ]);
        $this->store = new PostgreSqlTimeoutStorage($connection, $config, $messageNormalizer, new ObjectNormalizer());
        $this->store->clear();
    }

    protected function tearDown(): void
    {
        // $this->store->clear();
    }

    public function testAdd(): void
    {
        $timeout = new TestTimeout('test_add', $this->clock->now()->subDays(1), 'test_value');
        $headers = new MessageHeaders([]);

        $this->store->add(TimeoutWrapper::wrap($timeout, $headers));

        $timeouts = $this->store->findByEndsAtBefore($this->clock->now());
        $this->assertCount(1, $timeouts);
    }

    public function testFindByEndsAtBefore(): void
    {
        $timeout = new TestTimeout('test_find_by_ends_at_before_1', $this->clock->now()->subDays(1), 'test_value');
        $headers = new MessageHeaders([]);

        $this->store->add(TimeoutWrapper::wrap($timeout, $headers));

        $timeouts = $this->store->findByEndsAtBefore($this->clock->now());
        $this->assertCount(1, $timeouts);

        $timeout = new TestTimeout('test_find_by_ends_at_before_2', $this->clock->now()->subDays(1), 'test_value');
        $headers = new MessageHeaders([]);

        $this->store->add(TimeoutWrapper::wrap($timeout, $headers));

        $timeouts = $this->store->findByEndsAtBefore($this->clock->now()->subDays(5));
        $this->assertEmpty($timeouts);
    }

    public function testFindByEndsAtBetween(): void
    {
        $timeout = new TestTimeout('test_find_by_ends_at_between_1', $this->clock->now()->subDays(1), 'test_value');
        $headers = new MessageHeaders([]);

        $this->store->add(TimeoutWrapper::wrap($timeout, $headers));

        $timeouts = $this->store->findByEndsAtBefore($this->clock->now());
        $this->assertCount(1, $timeouts);

        $timeout = new TestTimeout('test_find_by_ends_at_between_2', $this->clock->now()->subDays(1), 'test_value');
        $headers = new MessageHeaders([]);

        $this->store->add(TimeoutWrapper::wrap($timeout, $headers));

        $timeouts = $this->store->findByEndsAtBefore($this->clock->now()->subDays(5));
        $this->assertEmpty($timeouts);
    }

    public function testRemove(): void
    {
        $timeout = new TestTimeout('test_remove', $this->clock->now()->subDays(1), 'test_value');
        $headers = new MessageHeaders([]);

        $this->store->add(TimeoutWrapper::wrap($timeout, $headers));

        $this->store->remove($timeout->getId());

        $timeouts = $this->store->findByEndsAtBefore($this->clock->now());

        $this->assertEmpty($timeouts);
    }
}
