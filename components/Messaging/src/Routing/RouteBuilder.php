<?php

namespace Morebec\Orkestra\Messaging\Routing;

/**
 * Inspects a {@link MessageHandlerInterface} through Reflection and extracts
 * the {@link MessageRouteInterface} it can support.
 */
class RouteBuilder
{
    public static function forMessageHandler(string $messageHandlerClassName): MessageHandlerRouteBuilder
    {
        return MessageHandlerRouteBuilder::forMessageHandler($messageHandlerClassName);
    }
}
