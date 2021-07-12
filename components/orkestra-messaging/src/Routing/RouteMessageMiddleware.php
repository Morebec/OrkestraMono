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
    private MessageRouterInterface $router;

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

    /**
     * Registers multiple routes.
     * If one of route is already registered, aborts the registration process.
     *
     * @param iterable|MessageRouteInterface[] $routes
     */
    public function registerRoutes(iterable $routes): self
    {
        $this->router->registerRoutes($routes);

        return $this;
    }

    /**
     * Registers a route with the router.
     * If this route is already registered, aborts the registration process.
     */
    public function registerRoute(MessageRouteInterface $route): self
    {
        $this->router->registerRoute($route);

        return $this;
    }
}
