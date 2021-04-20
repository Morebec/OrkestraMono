<?php

namespace Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\MessageHandlerInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Represents a route for a given {@link MessageInterface} to a {@link MessageHandlerInterface}.
 */
interface MessageRouteInterface
{
    /**
     * Indicates.
     */
    public function matches(MessageInterface $message, MessageHeaders $messageHeaders): bool;

    /**
     * Returns the unique ID of the route given its message type and handler.
     */
    public function getId(): string;

    /**
     * Returns the  Message Type Name.
     */
    public function getMessageTypeName(): string;

    /**
     * Returns the name of the Message Handler Method Name.
     */
    public function getMessageHandlerMethodName(): string;

    /**
     * Returns the Class Name of the  Message Handler.
     */
    public function getMessageHandlerClassName(): string;
}
