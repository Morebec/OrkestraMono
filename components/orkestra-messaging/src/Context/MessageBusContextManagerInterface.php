<?php

namespace Morebec\Orkestra\Messaging\Context;

use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * The Context Manager is responsible for managing the current message bus context.
 * It should be used solely by the responsible middleware of the {@link MessageBusInterface},
 * namely the {@link BuildMessageBusContextMiddleware}.
 */
interface MessageBusContextManagerInterface
{
    /**
     * Starts a new context for a given message with headers.
     */
    public function startContext(MessageInterface $message, MessageHeaders $headers): void;

    /**
     * Ends the currently active context or throw an exception if there was no started context.
     *
     * @throws NoMessageBusContextToEndException
     */
    public function endContext(): void;

    /**
     * Returns the current context or null if there is none.
     */
    public function getContext(): ?MessageBusContext;
}
