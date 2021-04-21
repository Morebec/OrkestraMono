<?php

namespace Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\MessageHandlerInterface;

/**
 * Allows the Routing components of the {@link MessageBusInterface} to be able to
 * to get an instance of a {@link MessageHandlerInterface}.
 */
interface MessageHandlerProviderInterface
{
    /**
     * Returns a Message Handler from a Class Name or returns null if not found.
     */
    public function getMessageHandler(string $messageHandlerClassName): ?MessageHandlerInterface;
}
