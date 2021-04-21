<?php

namespace Tests\Morebec\Orkestra\EventSourcing;

use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;

class TestEvent implements DomainEventInterface
{
    public static function getTypeName(): string
    {
        return 'test.event';
    }
}
