<?php

namespace Morebec\Orkestra\Messaging\Domain;

use Morebec\Orkestra\Messaging\MessageHandlerInterface;

/**
 * Interface for Domain Message Handlers.
 * Since PHP does not support Generics, an implicit contract must be followed when implementing this interface.
 * To indicate that they support a given message, the handlers should have public methods taking the supported message
 * as its single signature argument (typed) and should return any value it desires. The returned value will be wrapped
 * by the calling {@link DomainMessageBusInterface} as a DomainResponse.
 * Alternatively it can directly return a {@link MessageBusResponseInterface} or a {@link MessageBusResponseStatusCode}.
 */
interface DomainMessageHandlerInterface extends MessageHandlerInterface
{
}
