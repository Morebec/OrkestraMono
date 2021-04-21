<?php

namespace Morebec\Orkestra\Messaging\Transformation;

use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Implementation of a {@link MessagingTransformerInterface} that only takes care of responses.
 */
abstract class AbstractResponseTransformer implements MessagingTransformerInterface
{
    /**
     * {@inheritDoc}
     */
    public function transformMessage(MessageInterface $message, MessageHeaders $headers): MessageInterface
    {
        return $message;
    }
}
