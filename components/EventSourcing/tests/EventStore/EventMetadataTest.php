<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventStore;

use Morebec\Orkestra\EventSourcing\EventStore\EventMetadata;
use PHPUnit\Framework\TestCase;

class EventMetadataTest extends TestCase
{
    public function testHasKey(): void
    {
        $meta = new EventMetadata();
        $this->assertFalse($meta->hasKey('key'));

        $meta = new EventMetadata(['key' => 'value']);
        $this->assertTrue($meta->hasKey('key'));
    }
}
