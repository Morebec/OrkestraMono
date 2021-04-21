<?php

namespace Tests\Morebec\Orkestra\Messaging;

use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageHandlerResponse;
use PHPUnit\Framework\TestCase;

class MessageHandlerResponseTest extends TestCase
{
    public function testGetHandlerName(): void
    {
        $response = new MessageHandlerResponse('test_handler', MessageBusResponseStatusCode::ACCEPTED(), 'test_payload');
        $this->assertEquals('test_handler', $response->getHandlerName());
    }
}
