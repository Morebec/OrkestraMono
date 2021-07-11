<?php

namespace Morebec\Orkestra\Messaging;

/**
 * A Message bus is responsible for sending messages to subscribed {@link MessageHandlerInterface}.
 * Internally it should support the use of middleware in order to allow users of this interface to
 * alter the behaviour of sending of message through the bus.
 */
interface MessageBusInterface
{
    /**
     * Sends a message through this bus.
     * This function should never fail and should always return a response.
     * The only cases where it is allowed to throw orkestra-exceptions is for cases of misconfiguration of the bus itself,
     * to indicate that there is a problem with the configuration of the message bus.
     * Otherwise all orkestra-exceptions that are thrown by handlers should be transformed to responses.
     *
     * @param MessageHeaders|null $headers additional optional headers to be sent with the message
     */
    public function sendMessage(MessageInterface $message, ?MessageHeaders $headers = null): MessageBusResponseInterface;
}
