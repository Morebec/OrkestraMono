<?php

namespace Tests\Morebec\Orkestra\Messaging\Timeout;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\Timeout\InMemoryTimeoutStorage;
use Morebec\Orkestra\Messaging\Timeout\TimeoutInterface;
use Morebec\Orkestra\Messaging\Timeout\TimeoutWrapper;
use PHPUnit\Framework\TestCase;

class InMemoryTimeoutStorageTest extends TestCase
{
    public function testFindByEndsAtBetween(): void
    {
        $storage = new InMemoryTimeoutStorage();
        $storage->add(TimeoutWrapper::wrap(new class() implements TimeoutInterface {
            public static function getTypeName(): string
            {
                return 'test_timeout';
            }

            public function getId(): string
            {
                return uniqid('timeout_', false);
            }

            public function getEndsAt(): DateTime
            {
                return new DateTime('2020-01-01');
            }
        }, new MessageHeaders()));

        $results = $storage->findByEndsAtBetween(new DateTime('2020-01-01'), new DateTime('2020-01-05'));
        self::assertCount(1, $results);
    }

    public function testRemove(): void
    {
        $storage = new InMemoryTimeoutStorage();
        $id = uniqid('timeout_', false);
        $storage->add(TimeoutWrapper::wrap(new class($id) implements TimeoutInterface {
            private string $id;

            public function __construct(string $id)
            {
                $this->id = $id;
            }

            public static function getTypeName(): string
            {
                return 'test_timeout';
            }

            public function getId(): string
            {
                return $this->id;
            }

            public function getEndsAt(): DateTime
            {
                return new DateTime('2020-01-01');
            }
        }, new MessageHeaders()));

        $storage->remove($id);

        $results = $storage->findByEndsAtBetween(new DateTime('2020-01-01'), new DateTime('2020-05-05'));

        self::assertCount(0, $results);
    }

    public function testFindByEndsAtBefore(): void
    {
        $storage = new InMemoryTimeoutStorage();
        $id = uniqid('timeout_', false);
        $storage->add(TimeoutWrapper::wrap(new class($id) implements TimeoutInterface {
            private string $id;

            public function __construct(string $id)
            {
                $this->id = $id;
            }

            public static function getTypeName(): string
            {
                return 'test_timeout';
            }

            public function getId(): string
            {
                return $this->id;
            }

            public function getEndsAt(): DateTime
            {
                return new DateTime('2020-01-01');
            }
        }, new MessageHeaders()));

        $results = $storage->findByEndsAtBefore(new DateTime('2020-01-02'));
        self::assertCount(1, $results);
    }

    public function testAdd(): void
    {
        $storage = new InMemoryTimeoutStorage();
        $storage->add(TimeoutWrapper::wrap(new class() implements TimeoutInterface {
            public static function getTypeName(): string
            {
                return 'test_timeout';
            }

            public function getId(): string
            {
                return uniqid('timeout_', false);
            }

            public function getEndsAt(): DateTime
            {
                return new DateTime('2020-01-01');
            }
        }, new MessageHeaders()));

        $results = $storage->findByEndsAtBetween(new DateTime('2020-01-01'), new DateTime('2020-01-05'));
        self::assertCount(1, $results);
    }
}
