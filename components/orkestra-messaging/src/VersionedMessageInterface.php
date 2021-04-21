<?php

namespace Morebec\Orkestra\Messaging;

/**
 * Extending Interface of a {@link MessageInterface} that allows to version them using an integer.
 * By standard definition, everytime a {@link MessageInterface} has its schema updated, the returned version
 * number of the method @see self::messageVersion} should be bumped to return the previous version + 1.
 * The initial version of a message is always 0 and should never be negative.
 */
interface VersionedMessageInterface extends MessageInterface
{
    /**
     * Returns the version of this message's schema.
     */
    public static function getMessageVersion(): int;
}
