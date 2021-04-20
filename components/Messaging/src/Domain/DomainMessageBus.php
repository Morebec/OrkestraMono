<?php

namespace Morebec\Orkestra\Messaging\Domain;

use Morebec\Orkestra\Messaging\MessageBus;
use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Class DomainMessageBus.
 */
class DomainMessageBus extends MessageBus implements DomainMessageBusInterface
{
    public function sendMessage(MessageInterface $message, ?MessageHeaders $headers = null): MessageBusResponseInterface
    {
        if (!($message instanceof DomainMessageInterface)) {
            throw new \InvalidArgumentException(sprintf('The Domain Message Bus expects a message of type DomainMessageInterface, got "%s".', get_debug_type($message)));
        }

        return parent::sendMessage($message, $headers);
    }
}
