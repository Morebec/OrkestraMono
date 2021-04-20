<?php

namespace Morebec\Orkestra\Messaging\Timer;

use Morebec\Orkestra\Messaging\MessageHeaders;

/**
 * A Timer publisher is responsible for publishing timers to the message bus or other locations when they are processed.
 */
interface TimerPublisherInterface
{
    public function publish(TimerInterface $timer, MessageHeaders $headers): void;
}
