<?php

namespace Morebec\Orkestra\Messaging;

/**
 * Interface for Message Handlers.
 * Since PHP does not support Generics, an implicit contract must be followed when implementing this interface.
 * To indicate that they support a given message, the handlers should have public methods taking the supported message
 * as its single signature argument (typed) and should return any value it desires. The returned value will be wrapped
 * by the calling {@link MessageBusInterface} as a Response.
 * Alternatively it can directly return a {@link ResponseInterface} or a {@link ResponseStatusCode}.
 *
 * As a convention it is recommended for Message Handlers only supporting a single message to use the `__invoke` method
 */
interface MessageHandlerInterface
{
}
