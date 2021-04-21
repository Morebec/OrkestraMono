<?php

namespace Morebec\Orkestra\Messaging\Authorization;

use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Abstract implementation that can serve to rapidly define new authorizers without needing to fully implement the {@link MessageAuthorizerInterface}.
 */
abstract class AbstractMessageAuthorizer implements MessageAuthorizerInterface
{
    public function preAuthorize(MessageInterface $message, MessageHeaders $headers): void
    {
    }

    public function postAuthorize(MessageInterface $message, MessageHeaders $headers, MessageBusResponseInterface $response): void
    {
    }

    public function supportsPreAuthorization(MessageInterface $message, MessageHeaders $headers): bool
    {
        return false;
    }

    public function supportsPostAuthorization(MessageInterface $message, MessageHeaders $headers, MessageBusResponseInterface $response): bool
    {
        return false;
    }
}
