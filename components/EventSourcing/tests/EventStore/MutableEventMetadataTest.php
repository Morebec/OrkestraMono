<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventStore;

use Morebec\Orkestra\EventSourcing\EventStore\MutableEventMetadata;
use PHPUnit\Framework\TestCase;

class MutableEventMetadataTest extends TestCase
{
    public function testPutValue(): void
    {
        $meta = new MutableEventMetadata();
        $meta->putValue('key', 'value');

        $this->assertTrue($meta->hasKey('key'));
    }

    public function testRemoveValue(): void
    {
        $meta = new MutableEventMetadata();
        $meta->putValue('key', 'value');
        $meta->removeKey('key');

        $this->assertFalse($meta->hasKey('key'));

        // Should not throw exception
        $meta->removeKey('key');
    }
}
