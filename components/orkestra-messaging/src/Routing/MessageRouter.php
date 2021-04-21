<?php

namespace Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Default Implementation of a {@link MessageRouterInterface}.
 */
class MessageRouter implements MessageRouterInterface
{
    /**
     * @var MessageRouteCollection
     */
    private $routes;

    public function __construct(iterable $routes = [])
    {
        $this->routes = new MessageRouteCollection();
        foreach ($routes as $route) {
            $this->registerRoute($route);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerRoute(MessageRouteInterface $route): void
    {
        $this->routes->add($route);
    }

    /**
     * {@inheritdoc}
     */
    public function registerRoutes(iterable $routes): void
    {
        foreach ($routes as $route) {
            $this->registerRoute($route);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function routeMessage(MessageInterface $message, MessageHeaders $headers): MessageRouteCollection
    {
        return $this->routes->filter(
            static function (MessageRouteInterface $route) use ($headers, $message) {
                return $route->matches($message, $headers);
            }
        );
    }

    public function getRoutes(): iterable
    {
        return $this->routes;
    }

    public function clearRoutes(): void
    {
        $this->routes->removeAll();
    }
}
