<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventProcessor\InMemoryEventStorePositionStorage;
use PHPUnit\Framework\TestCase;

class InMemoryEventStorePositionStorageTest extends TestCase
{
    public function testSet(): void
    {
        $storage = new InMemoryEventStorePositionStorage();
        $storage->set('test', 50);
        $this->assertEquals(50, $storage->get('test'));

        $storage->set('test', 150);
        $this->assertEquals(150, $storage->get('test'));
    }

    public function testReset(): void
    {
        $storage = new InMemoryEventStorePositionStorage();
        $storage->set('test', 50);
        $storage->reset('test');

        $this->assertNull($storage->get('test'));
    }

    public function testGet(): void
    {
        $storage = new InMemoryEventStorePositionStorage();
        $this->assertNull($storage->get('test'));

        $storage->set('test', 150);
        $this->assertEquals(150, $storage->get('test'));
    }
}
