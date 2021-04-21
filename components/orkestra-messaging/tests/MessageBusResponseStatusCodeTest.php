<?php

namespace Tests\Morebec\Orkestra\Messaging;

use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use PHPUnit\Framework\TestCase;

class MessageBusResponseStatusCodeTest extends TestCase
{
    public function testFromString(): void
    {
        $accepted = MessageBusResponseStatusCode::fromString(MessageBusResponseStatusCode::ACCEPTED);
        $this->assertEquals($accepted, MessageBusResponseStatusCode::ACCEPTED());

        $this->expectException(\InvalidArgumentException::class);
        MessageBusResponseStatusCode::fromString('NOT_VALID');
    }
}
