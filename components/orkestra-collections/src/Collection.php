<?php

namespace Morebec\Orkestra\Collections;

/**
 * @template T
 */
class Collection implements \Iterator, \Countable
{
    /** @var T[] */
    protected array $elements;

    /**
     * @param T[] $elements
     */
    public function __construct(iterable $elements = [], bool $preserveKeys = false)
    {
        $this->elements = [];

        foreach ($elements as $key => $element) {
            if ($preserveKeys) {
                $this->addAt($key, $element);
            } else {
                $this->add($element);
            }
        }
    }

    /**
     * Groups values by keys.
     * The callable takes a key and a value and returns the key that the provided value belongs to.
     *
     * @return static<T>
     */
    public function groupBy(callable $callable, bool $preserveKeys = false): self
    {
        $groups = [];
        foreach ($this->elements as $key => $element) {
            $groupKey = $callable($key, $element);
            if (!\array_key_exists($groupKey, $groups)) {
                $groups[$groupKey] = [];
            }
            if ($preserveKeys) {
                $groups[$groupKey][$key] = $element;
            } else {
                $groups[$groupKey][] = $element;
            }
        }

        return new self($groups, true);
    }

    /**
     * Flattens each {@link \Iterator} element of this collection as a new collection.
     *
     * @return static<T>
     */
    public function flatten(): self
    {
        $return = [];
        foreach ($this->elements as $element) {
            if ($element instanceof \Iterator) {
                foreach ($element as $e) {
                    $return[] = $e;
                }
            } else {
                $return[] = $element;
            }
        }

        return new self($return);
    }

    /**
     * Projects each element of this collection into a new collection preserving
     * the index.
     *
     * @return static<T>
     */
    public function map(callable $p): self
    {
        return new self(array_map($p, $this->elements));
    }

    /**
     * Filters this collection and returns a new collection with the results.
     *
     * @return static<T>
     */
    public function filter(callable $p): self
    {
        return new self(array_values(array_filter($this->elements, $p)));
    }

    /**
     * Indicates if all elements of this collection satisfy a condition.
     */
    public function areAll(callable $p): bool
    {
        $c = $this->filter($p);

        return $c->count() === $this->count();
    }

    /**
     * Indicates if any elements of this collection satisfies a condition.
     */
    public function isAny(callable $p): bool
    {
        $c = $this->findFirstOrDefault($p);

        return $c !== null;
    }

    /**
     * Returns the element at a given index.
     *
     * @param string|int $index
     *
     * @return T
     */
    public function get($index)
    {
        return $this->elements[$index];
    }

    /**
     * Returns the element at a given index or a default value it is out of rance.
     *
     * @param string|int $index
     * @param null       $default
     *
     * @return T|mixed
     */
    public function getOrDefault($index, $default = null)
    {
        return \array_key_exists($index, $this->elements) ? $this->get($index) : $default;
    }

    /**
     * Returns the first element.
     *
     * @return T
     */
    public function getFirst()
    {
        $firstKey = array_key_first($this->elements);

        return $this->get($firstKey);
    }

    /**
     * Finds the first element matching a search predicate and returns it,
     * or returns a default value if none matched.
     *
     * @param mixed $default
     *
     * @return T|null
     */
    public function findFirstOrDefault(callable $p, $default = null)
    {
        foreach ($this->elements as $e) {
            if ($p($e)) {
                return $e;
            }
        }

        return $default;
    }

    /**
     * Returns the last element.
     *
     * @return T
     */
    public function getLast()
    {
        $lastKey = array_key_last($this->elements);

        return $this->get($lastKey);
    }

    /**
     * Inverts the order of the elements of this collection and returns it as a new collection.
     *
     * @returns static<T>
     */
    public function reversed(): self
    {
        return new self(array_reverse($this->elements));
    }

    /**
     * Extract a slice of the collection as a new collection.
     *
     * @param int      $offset
     *                         If offset is non-negative, the sequence will
     *                         start at that offset in the array. If
     *                         offset is negative, the sequence will
     *                         start that far from the end of the array.
     * @param int|null $length
     *                         If length is given and is positive, then
     *                         the sequence will have that many elements in it. If
     *                         length is given and is negative then the
     *                         sequence will stop that many elements from the end of the
     *                         array. If it is omitted, then the sequence will have everything
     *                         from offset up until the end of the
     *                         array.
     */
    public function slice(int $offset, ?int $length = null): self
    {
        $elements = \array_slice($this->elements, $offset, $length);

        return new self($elements);
    }

    /**
     * Splits this collection into a collection of orkestra-collections.
     * Each contained collection will have $length elements or less (for the last one).
     */
    public function chunk(int $length): self
    {
        if ($length < 1) {
            throw new \InvalidArgumentException(sprintf('The length must be a positive integer, received "%s".', $length));
        }

        $chunks = array_chunk($this->elements, $length);

        $collection = new self();
        foreach ($chunks as $chunk) {
            $collection->add(new self($chunk));
        }

        return $collection;
    }

    /**
     * Applies an accumulator callback over the elements of this collection.
     *
     * @param callable   $p
     *                            The callback function. Signature is <pre>callback ( mixed $carry , mixed $element ) : mixed</pre>
     *                            <blockquote>mixed <var>$carry</var> <p>The return value of the previous iteration; on the first iteration it holds the value of <var>$initial</var>.</p></blockquote>
     *                            <blockquote>mixed <var>$element</var> <p>Holds the current iteration value of the <var>$input</var></p></blockquote>
     *                            </p>
     * @param mixed|null $initial
     *
     * @return mixed
     */
    public function reduce(callable $p, $initial = null)
    {
        return array_reduce($this->elements, $p, $initial);
    }

    /**
     * Appends a value to the end of this collection.
     *
     * @param T $element
     */
    public function add($element): void
    {
        $this->elements[] = $element;
    }

    /**
     * Adds a value to this collection at a specified key.
     *
     * @param mixed $key
     * @param T     $element
     */
    public function addAt($key, $element): void
    {
        $this->elements[$key] = $element;
    }

    /**
     * Adds an element at the beginning of this collection.
     *
     * @param T $element
     */
    public function prepend($element): void
    {
        array_unshift($this->elements, $element);
    }

    /**
     * Clears this array removing all elements it contains.
     */
    public function clear(): void
    {
        $this->elements = [];
    }

    /**
     * Converts this collection to an Array.
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    public function current()
    {
        return current($this->elements);
    }

    public function next(): void
    {
        next($this->elements);
    }

    public function key()
    {
        return key($this->elements);
    }

    public function valid(): bool
    {
        return \array_key_exists($this->key(), $this->elements);
    }

    public function rewind(): void
    {
        reset($this->elements);
    }

    public function count(): int
    {
        return \count($this->elements);
    }

    /**
     * Typed alias of self::count().
     */
    public function getCount(): int
    {
        return $this->count();
    }

    /**
     * Indicates if this collection is empty.
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Returns a copy of this collection.
     *
     * @return static<T>
     */
    public function copy(): self
    {
        return new self($this->elements);
    }
}
