<?php

namespace Morebec\Orkestra\Messaging\Routing;

use Countable;
use InvalidArgumentException;
use Iterator;

/**
 * Utility class allowing to manage collections of routes.
 */
class MessageRouteCollection implements Iterator, Countable
{
    /**
     * Mapping between route Ids and their index in the list of routes.
     * This is used to ensure routes are unique while preserving their order.
     *
     * @var int[]
     */
    private $routeIdIndexMap;

    /**
     * Ordered list of routes.
     *
     * @var MessageRouteInterface[]
     */
    private $routes;

    public function __construct(iterable $routes = [])
    {
        $this->routes = [];
        $this->routeIdIndexMap = [];
        $this->addAll($routes);
    }

    /**
     * Adds a route to this Collection.
     * If the route is already part of this collection silently returns.
     * see {@link MessageRouteCollection::has()} for details about equality.
     */
    public function add(MessageRouteInterface $route): void
    {
        if ($this->has($route)) {
            return;
        }

        $index = \count($this->routes);
        $this->routes[] = $route;
        $this->routeIdIndexMap[$route->getId()] = $index;
    }

    /**
     * Appends all of the elements in the specified iterable collection to the end of this collection, in the order
     * that they are returned by the specified iterable collection.
     */
    public function addAll(iterable $routes): void
    {
        foreach ($routes as $route) {
            $this->add($route);
        }
    }

    /**
     * Removes a route from this collection by its ID.
     */
    public function remove(MessageRouteInterface $route): void
    {
        $routeId = $route->getId();
        if (!$this->has($route)) {
            throw new InvalidArgumentException(sprintf('Route with ID "%s" was not found', $routeId));
        }

        // Remove from the routes and the map
        $index = $this->routeIdIndexMap[$routeId];
        unset($this->routes[$index]);

        // Save routes and maps and read to preserve ordering.
        $routes = $this->routes;

        $this->routes = [];
        $this->routeIdIndexMap = [];
        foreach ($routes as $route) {
            $this->add($route);
        }
    }

    /**
     * Removes all the routes from this collection.
     */
    public function removeAll(): void
    {
        $this->routes = [];
        $this->routeIdIndexMap = [];
    }

    /**
     * Filters this collection of routes for a given predicate and return a
     * new Collection.
     */
    public function filter(callable $predicate): self
    {
        $routes = array_filter($this->routes, $predicate);

        return new self($routes);
    }

    public function toArray(): array
    {
        return $this->routes;
    }

    /**
     * @return MessageRouteInterface|false
     */
    public function current()
    {
        return current($this->routes);
    }

    /**
     * @return MessageRouteInterface|false
     */
    public function next()
    {
        return next($this->routes);
    }

    /**
     * @return int|null
     */
    public function key()
    {
        return key($this->routes);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return \array_key_exists($this->key(), $this->routes);
    }

    public function rewind()
    {
        reset($this->routes);
    }

    /**
     * @return int
     */
    public function count()
    {
        return \count($this->routes);
    }

    /**
     * Indicates if a route is found within this collection. To determine this
     * it checks the route's ID.
     */
    protected function has(MessageRouteInterface $route): bool
    {
        return \array_key_exists($route->getId(), $this->routeIdIndexMap);
    }
}
