<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventStore;

use Morebec\Orkestra\EventSourcing\EventStore\EventData;
use PHPUnit\Framework\TestCase;

class EventDataTest extends TestCase
{
    public function testHasKey(): void
    {
        $data = new EventData();
        $this->assertFalse($data->hasKey('key'));

        $data = new EventData(['key' => 'value']);
        $this->assertTrue($data->hasKey('key'));
    }
}
