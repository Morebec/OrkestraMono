<?php

namespace Morebec\Orkestra\Normalization;

use Closure;
use TypeError;

/**
 * Implementation of an object accessor binding a Closure to the instance of an object
 * in order to be able to call $this on it.
 * It must be noted that this actually allows to "enter" the private scope of an object.
 * If a class is inherited, it won't have access to the parent's private variables, since
 * it is basically just like doing $this->property but outside the object.
 */
class ObjectAccessor implements ObjectAccessorInterface
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var Closure
     */
    private $reader;

    /**
     * @var Closure
     */
    private $writer;

    public function __construct(object $object)
    {
        $this->object = $object;
        $this->reader = function &($object, $propertyName) {
            $invoke = Closure::bind(function &() use ($propertyName) {
                return $this->$propertyName;
            }, $object, $object)->__invoke();
            $value = &$invoke;

            return $value;
        };

        $this->writer = function ($object, $propertyName, $value) {
            Closure::bind(function () use ($propertyName, $value) {
                if (!property_exists($this, $propertyName)) {
                    $class = static::class;
                    $implode = implode(', ', array_keys(get_class_vars($class)));
                    throw new TypeError("Property '$propertyName' does not exist on class '$class'. Available properties are {$implode}");
                }

                return $this->$propertyName = $value;
            }, $object, $object)->__invoke();
        };
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->readProperty($name);
    }

    /**
     * @param mixed $value
     */
    public function __set(string $name, $value): void
    {
        $this->writeProperty($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public static function access(object $object): ObjectAccessorInterface
    {
        return new self($object);
    }

    /**
     * Reads the property of an object.
     *
     * @return mixed
     */
    public function readProperty(string $propertyName)
    {
        $reader = $this->reader;

        return $reader($this->object, $propertyName);
    }

    /**
     * Writes the property of an object.
     *
     * @param mixed $value
     */
    public function writeProperty(string $propertyName, $value): void
    {
        $writer = $this->writer;
        $writer($this->object, $propertyName, $value);
    }

    /**
     * Indicates if the object inspected by this accessor
     * has a certain property.
     */
    public function hasProperty(string $propertyName): bool
    {
        return property_exists($this->object, $propertyName);
    }

    public function getProperties(): array
    {
        $reader = function &($object) {
            $invoke = Closure::bind(function &() {
                return array_keys(get_object_vars($this));
            }, $object, $object)->__invoke();
            $value = &$invoke;

            return $value;
        };

        return $reader($this->object);
    }
}
