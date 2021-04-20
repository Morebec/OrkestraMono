<?php

namespace Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\MessageHandlerInterface;

/**
 * Message Handler interceptors can intercept messages right before they are handled by
 * a given {@link MessageHandlerInterface} to alter the message.
 * It is an alternative to creating custom middleware, by providing an area of action that is closer to the
 * handling of messages.
 * Just like middleware interceptors can be used for a wide range of uses cases such as:
 * - Validation
 * - Authorization
 * - Authentication
 * - Monitoring
 * - Tenant Specific work etc.
 */
interface MessageHandlerInterceptorInterface
{
    /**
     * Called right before a message is sent to a specific message handler.
     * This method should never throw an exception.
     */
    public function beforeHandle(MessageHandlerInterceptionContext $context): void;

    /**
     * Called right after a message was sent and handled by a specific message handler.
     * This method should never throw an exception.
     */
    public function afterHandle(MessageHandlerInterceptionContext $context): void;
}
