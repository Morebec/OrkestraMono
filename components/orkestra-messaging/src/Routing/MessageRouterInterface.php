<?php

namespace Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * The Message Router is used by the {@link MessageBusInterface}'s middleware
 * to route a message to the right handlers.
 */
interface MessageRouterInterface
{
    /**
     * Registers a route with the router.
     * If this route is already registered, aborts the registration process.
     */
    public function registerRoute(MessageRouteInterface $route): void;

    /**
     * Registers multiple routes.
     * If one of route is already registered, aborts the registration process.
     *
     * @param iterable|MessageRouteInterface[] $routes
     */
    public function registerRoutes(iterable $routes): void;

    /**
     * Routes a certain message, i.e. it returns the routes that match a given message.
     */
    public function routeMessage(MessageInterface $message, MessageHeaders $headers): MessageRouteCollection;

    /**
     * Returns the list of routes registered with this router.
     *
     * @return iterable|MessageRouteInterface[]
     */
    public function getRoutes(): iterable;

    /**
     * Removes all the routes registered with this router.
     */
    public function clearRoutes(): void;
}
