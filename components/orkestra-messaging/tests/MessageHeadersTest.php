<?php

namespace Tests\Morebec\Orkestra\Messaging;

use Morebec\Orkestra\Messaging\MessageHeaders;
use PHPUnit\Framework\TestCase;

class MessageHeadersTest extends TestCase
{
    public function testConstruct(): void
    {
        $headers = new MessageHeaders([
            MessageHeaders::MESSAGE_TYPE => 'testMessage',
        ]);

        $this->assertNotNull($headers);
        $this->assertTrue($headers->has(MessageHeaders::MESSAGE_TYPE));
    }

    public function testHas(): void
    {
        $headers = new MessageHeaders([
            MessageHeaders::MESSAGE_TYPE => 'testMessage',
        ]);

        $this->assertNotNull($headers);
        $this->assertTrue($headers->has(MessageHeaders::MESSAGE_TYPE));
        $this->assertFalse($headers->has(MessageHeaders::SENT_AT));
    }

    public function testToArray(): void
    {
        $values = [
            MessageHeaders::MESSAGE_TYPE => 'testMessage',
        ];
        $headers = new MessageHeaders($values);

        $this->assertEquals($values, $headers->toArray());

        $headers = new MessageHeaders();

        $this->assertEmpty($headers->toArray());
    }

    public function testSet(): void
    {
        $headers = new MessageHeaders();
        $headers->set('hello', 'world');
        $this->assertEquals('world', $headers->get('hello'));
    }

    public function testGet(): void
    {
        $headers = new MessageHeaders();
        $headers->set('hello', 'world');

        $this->assertEquals('world', $headers->get('hello'));
        $this->assertEquals('world', $headers->get('hello', 'default_value'));
        $this->assertEquals('default_value', $headers->get('not_here', 'default_value'));
    }

    public function testClone(): void
    {
        $values = [
            MessageHeaders::MESSAGE_TYPE => 'testMessage',
        ];
        $headers = new MessageHeaders($values);

        $headersCopy = $headers->copy();

        $this->assertNotSame($headers, $headersCopy);
        $this->assertEquals($headers, $headersCopy);
    }
}
