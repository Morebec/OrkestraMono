<?php

namespace Morebec\Orkestra\Messaging\Transformation;

use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Transformers act as interceptors of messages and responses.
 * They are triggered by the {@link MessagingTransformationMiddleware}.
 */
interface MessagingTransformerInterface
{
    /**
     * Transforms a message.
     * Implementations if they do not wish to transform the message under certain circumstances, can simply return the message early.
     * The headers can be modified freely.
     */
    public function transformMessage(MessageInterface $message, MessageHeaders $headers): MessageInterface;

    /**
     * Transforms a response.
     * Implementations if they do not wish to transform the response under certain circumstances, can simply return the response early.
     * Advice: The message headers should conceptually not be modified since the message was already handled at this point,
     * but certain advanced use cases might require, in these cases, it should be documented explicitly in the transformers
     * documentation.
     */
    public function transformResponse(MessageBusResponseInterface $response, MessageInterface $message, MessageHeaders $headers): MessageBusResponseInterface;
}
