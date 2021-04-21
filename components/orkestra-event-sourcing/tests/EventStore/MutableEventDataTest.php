<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventStore;

use Morebec\Orkestra\EventSourcing\EventStore\MutableEventData;
use PHPUnit\Framework\TestCase;

class MutableEventDataTest extends TestCase
{
    public function testPutValue(): void
    {
        $data = new MutableEventData();
        $data->putValue('key', 'value');

        $this->assertTrue($data->hasKey('key'));
    }

    public function testRemoveValue(): void
    {
        $data = new MutableEventData();
        $data->putValue('key', 'value');
        $data->removeKey('key');

        $this->assertFalse($data->hasKey('key'));

        // Should not throw exception
        $data->removeKey('key');
    }
}
