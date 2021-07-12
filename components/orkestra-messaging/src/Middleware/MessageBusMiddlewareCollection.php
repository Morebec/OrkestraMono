<?php

namespace Morebec\Orkestra\Messaging\Middleware;

/**
 * Represents a collection of middleware.
 */
class MessageBusMiddlewareCollection implements \Countable, \Iterator
{
    /** @var MessageBusMiddlewareInterface[] */
    private array $middleware;

    public function __construct(iterable $middleware = [])
    {
        $this->middleware = [];
        foreach ($middleware as $m) {
            $this->append($m);
        }
    }

    /**
     * Appends new middleware to the end of this collection.
     */
    public function append(MessageBusMiddlewareInterface $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Prepends new middleware at the beginning of this collection.
     */
    public function prepend(MessageBusMiddlewareInterface $middleware): void
    {
        array_unshift($this->middleware, $middleware);
    }

    /**
     * Adds middleware right before another specific one.
     *
     * @param string $middlewareClassName
     */
    public function addBefore(string $followingMiddlewareClassName, MessageBusMiddlewareInterface $middleware): void
    {
        $foundIndex = null;
        foreach ($this->middleware as $key => $m) {
            if (\get_class($m) === $followingMiddlewareClassName) {
                $foundIndex = $key;
                break;
            }
        }

        if ($foundIndex === null) {
            throw new \InvalidArgumentException("Middleware \"$followingMiddlewareClassName\" was not found.");
        }

        array_splice($this->middleware, $foundIndex, 0, [$middleware]);
    }

    /**
     * Adds middleware right after a specific one.
     */
    public function addAfter(string $precedingMiddlewareCLassName, MessageBusMiddlewareInterface $middleware): void
    {
        $foundIndex = null;
        foreach ($this->middleware as $key => $m) {
            if (\get_class($m) === $precedingMiddlewareCLassName) {
                $foundIndex = $key;
                break;
            }
        }

        if ($foundIndex === null) {
            throw new \InvalidArgumentException("Middleware \"$precedingMiddlewareCLassName\" was not found.");
        }

        array_splice($this->middleware, $foundIndex + 1, 0, [$middleware]);
    }

    /**
     * Returns the middleware at a given index or null.
     */
    public function getOrDefault(int $index, ?MessageBusMiddlewareInterface $default = null): ?MessageBusMiddlewareInterface
    {
        return $this->middleware[$index] ?? $default;
    }

    /**
     * Returns an array representation of this collection.
     */
    public function toArray(): array
    {
        return $this->middleware;
    }

    public function current()
    {
        return current($this->middleware);
    }

    public function next(): void
    {
        next($this->middleware);
    }

    public function key()
    {
        return key($this->middleware);
    }

    public function valid(): bool
    {
        return \array_key_exists($this->key(), $this->middleware);
    }

    public function rewind(): void
    {
        reset($this->middleware);
    }

    public function count(): int
    {
        return \count($this->middleware);
    }
}
