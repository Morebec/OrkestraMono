<?php

namespace Morebec\Orkestra\OrkestraServer\Messaging;

use Morebec\Orkestra\Messaging\MessageBusInterface;

/**
 * Message Bus responsible for forwarding messages received to registered services.
 */
interface ServiceMessageBusInterface extends MessageBusInterface
{
}
