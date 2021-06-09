<?php

namespace Morebec\Orkestra\Messaging\Timeout;

use Morebec\Orkestra\Messaging\MessageHeaders;

/**
 * A Timeout publisher is responsible for publishing timeouts to the message bus or other locations when they are processed.
 */
interface TimeoutPublisherInterface
{
    public function publish(TimeoutInterface $timeout, MessageHeaders $headers): void;
}
