<?php

namespace Morebec\Orkestra\Messaging\Authorization;

use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Service responsible for the Authorization of the handling of a {@link MessageInterface}.
 * They can be used by the {@link AuthorizeMessageMiddleware}.
 */
interface MessageAuthorizerInterface
{
    /**
     * Authorizes a {@link MessageInterface} that was sent on the {@link MessageBusInterface} with some given
     * {@link MessageHeaders}, **before** it is sent to the next middleware.
     * If the message is authorized, simply returns silently. I
     * f the access is denied, it will throw an {@link UnauthorizedException}.
     *
     * @throws UnauthorizedException
     */
    public function preAuthorize(MessageInterface $message, MessageHeaders $headers): void;

    /**
     * Authorizes a {@link MessageInterface} that was sent on the {@link MessageBusInterface} with some given
     * {@link MessageHeaders}, **after** a {@link MessageBusResponseInterface} was received.
     * If the message is authorized, simply returns silently. I
     * f the access is denied, it will throw an {@link UnauthorizedException}.
     *
     * @throws UnauthorizedException
     */
    public function postAuthorize(MessageInterface $message, MessageHeaders $headers, MessageBusResponseInterface $response): void;

    /**
     * Indicates if this {@link MessageAuthorizerInterface} is able to perform a preAuthorization for a specific {@link MessageInterface}
     * with some given {@link MessageHeaders} or not.
     */
    public function supportsPreAuthorization(MessageInterface $message, MessageHeaders $headers): bool;

    /**
     * Indicates if this {@link MessageAuthorizerInterface} is able to perform a postAuthorization for a specific {@link MessageInterface}
     * with some given {@link MessageHeaders} and a given {@link MessageBusResponseInterface} or not.
     */
    public function supportsPostAuthorization(MessageInterface $message, MessageHeaders $headers, MessageBusResponseInterface $response): bool;
}
