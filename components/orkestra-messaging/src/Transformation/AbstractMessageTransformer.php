<?php

namespace Morebec\Orkestra\Messaging\Transformation;

use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Implementation of a {@link MessagingTransformerInterface} that only takes care of messages.
 */
abstract class AbstractMessageTransformer implements MessagingTransformerInterface
{
    /**
     * {@inheritDoc}
     */
    public function transformResponse(MessageBusResponseInterface $response, MessageInterface $message, MessageHeaders $headers): MessageBusResponseInterface
    {
        return $response;
    }
}
