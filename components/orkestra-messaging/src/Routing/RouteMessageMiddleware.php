<?php

namespace Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareInterface;

/**
 * Middleware responsible for finding the routes to {@link MessageHandlerInterface} that match a specific {@link MessageInterface}.
 * Once resolved these routes are added to the {@link MessageHeaders}.
 * The reason for having this decoupled from the {@link HandleMessageMiddleware} is to allow other middleware
 * to manipulate these routes in the headers prior to the messages getting sent ot their resolved handlers.
 */
class RouteMessageMiddleware implements MessageBusMiddlewareInterface
{
    /**
     * @var MessageRouterInterface
     */
    private $router;

    public function __construct(MessageRouterInterface $router)
    {
        $this->router = $router;
    }

    public function __invoke(MessageInterface $message, MessageHeaders $headers, callable $next): MessageBusResponseInterface
    {
        // Only resolve routes using the router if it is not already set in the headers.

        $destinationHandlers = $headers->get(MessageHeaders::DESTINATION_HANDLER_NAMES);

        if (!$destinationHandlers) {
            $routes = $this->router->routeMessage($message, $headers);
            $routesAsString = array_map(static function (MessageRouteInterface $r) {
                return "{$r->getMessageHandlerClassName()}::{$r->getMessageHandlerMethodName()}";
            }, $routes->toArray());

            $headers->set(MessageHeaders::DESTINATION_HANDLER_NAMES, $routesAsString);
        }

        return $next($message, $headers, $next);
    }
}
