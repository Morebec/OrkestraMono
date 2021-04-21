<?php

namespace Morebec\Orkestra\Messaging;

/**
 * Represents a message that can be sent through the Message bus.
 * Messages serve as a contract between listening endpoints and their handlers.
 * Note: Messages should only contain primitive values for serialization purposes.
 */
interface MessageInterface
{
    /**
     * Returns the name of this message.
     */
    public static function getTypeName(): string;
}
