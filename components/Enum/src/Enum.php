<?php

namespace Morebec\Orkestra\Enum;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

/**
 * Simple implementation of an Enum type for PHP.
 * This allows to simplify the creation Enum classes using constants by relying on Reflection.
 * It allows to have typed enums instead of relying on primitive types for enumerations of values.
 */
abstract class Enum
{
    /** @var mixed value */
    protected $value;

    /**
     * Cache for reflection.
     *
     * @var array
     */
    private static $constCacheArray = null;

    final public function __construct($value)
    {
        static::validateValue($value);
        $this->value = $value;
    }

    /**
     * Makes it possible to do static calls in the form
     * Enum::VALUE_NAME().
     *
     * @param string $method
     * @param string $argument
     *
     * @return Enum
     */
    public static function __callStatic($method, $argument)
    {
        if (!static::isValidName($method)) {
            throw new InvalidArgumentException(sprintf('Undefined Enum Name %s for %s', $method, static::class));
        }
        $value = \constant("static::$method");

        return new static($value);
    }

    public function __toString(): string
    {
        return (string) $this->getValue();
    }

    /**
     * Indicates if a certain name is considered a valid name for this enum
     * based on the constants it provides.
     *
     * @param string $name               name of the enum value to test
     * @param bool   $caseSensitiveCheck indicates if the test should be case sensitive
     */
    public static function isValidName(string $name, bool $caseSensitiveCheck = true): bool
    {
        $constants = static::getConstants();

        if ($caseSensitiveCheck) {
            return \array_key_exists($name, $constants);
        }

        $keys = array_map('strtoupper', array_keys($constants));

        return \in_array(strtoupper($name), $keys);
    }

    /**
     * Indicates if a value is a valid value for this enum.
     *
     * @param mixed $value
     * @param bool  $strictCheck when true, will do strict equality checks
     */
    public static function isValidValue($value, bool $strictCheck = true): bool
    {
        if ($value instanceof static) {
            return true;
        }

        $values = array_values(static::getConstants());

        return \in_array($value, $values, $strictCheck);
    }

    /**
     * Indicates if this instance of an enum value is equal to another one.
     */
    public function isEqualTo(self $value): bool
    {
        return $this->value === $value->value;
    }

    /**
     * Returns the primitive behind this Enum value.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the possible values of this enum.
     *
     * @throws ReflectionException
     */
    public static function getValues(): array
    {
        return array_values(self::getConstants());
    }

    protected static function validateValue($value)
    {
        if (!static::isValidValue($value)) {
            $values = implode("', '", static::getConstants());
            throw new InvalidArgumentException("Invalid Enum value: '{$value}', valid values: ['{$values}']");
        }
    }

    /**
     * Returns the names and values of the constants of this class,
     * where keys are the constant names and values their constant value.
     *
     * @return string[]
     *
     * @throws ReflectionException
     */
    protected static function getConstants(): array
    {
        if (self::$constCacheArray === null) {
            self::$constCacheArray = [];
        }

        // Load the constants in memory
        $calledClass = static::class;
        if (!\array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }

        return self::$constCacheArray[$calledClass];
    }
}
