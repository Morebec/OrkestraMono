<?php

namespace Morebec\Orkestra\Normalization;

/**
 * The Object accessor is used to manipulate object properties (public, protected and private) at runtime
 * in an efficient way. It is mostly used to easily create mappers for persistence.
 * It alleviates the need to rely on other patterns such as the memento pattern to extract
 * the current state of entities, keeping them clean, from persistence logic.
 * This should not be used to bypass object encapsulation whenever one wants, only in very specific cases:
 * - Serializers.
 * - Mappers.
 * - Hydrators.
 * - Normalizers.
 */
interface ObjectAccessorInterface
{
    /**
     * Returns a new accessor instance for a given object.
     */
    public static function access(object $object): self;

    /**
     * Reads the property of an object.
     *
     * @return mixed
     */
    public function readProperty(string $propertyName);

    /**
     * Writes the property of an object.
     *
     * @param mixed $value
     */
    public function writeProperty(string $propertyName, $value): void;

    /**
     * Indicates if the object inspected by this accessor
     * has a certain property.
     */
    public function hasProperty(string $propertyName): bool;

    /**
     * Returns the list of all property names.
     */
    public function getProperties(): array;
}
