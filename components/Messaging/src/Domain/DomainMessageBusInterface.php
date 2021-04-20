<?php

namespace Morebec\Orkestra\Messaging\Domain;

use Morebec\Orkestra\Messaging\MessageBusInterface;

/**
 * A Domain message bus is responsible for sending messages to subscribed {@link DomainMessageHandlerInterface}.
 */
interface DomainMessageBusInterface extends MessageBusInterface
{
}
