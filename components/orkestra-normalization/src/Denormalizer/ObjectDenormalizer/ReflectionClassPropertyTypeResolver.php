<?php

namespace Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer;

use PhpDocReader\AnnotationException;
use PhpDocReader\PhpParser\UseStatementParser;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;

/**
 * Responsible for detecting the type of a property on a class using reflection.
 */
class ReflectionClassPropertyTypeResolver
{
    /**
     * @var UseStatementParser
     */
    private $parser;

    private $builtInTypes = [
        'bool',
        'null',
        'boolean',
        'string',
        'int',
        'integer',
        'float',
        'double',
        'array',
        'object',
        'callable',
        'resource',
        'mixed',
        'iterable',
    ];

    public function __construct()
    {
        $this->parser = new UseStatementParser();
    }

    /**
     * Detects the types of a property using the following strategies:
     * 1. Checks the PHP Version to see if it is retrievable PHP 7.4 and onwards.
     * 2. Check `var` annotation in phpDocComment
     * 3. Check the return type of a potential getter.
     *
     * If no types have been detected, returns an empty array.
     *
     * @return string[]
     */
    public function detectPropertyType(ReflectionProperty $property): array
    {
        // PHP 7.4
        if ($types = $this->detectFromReflection($property)) {
            return $types;
        }

        // phpDocComment
        if ($types = $this->detectFromPhpDocComment($property)) {
            return $types;
        }

        // Getters
        if ($types = $this->detectFromGetters($property)) {
            return $types;
        }

        return [];
    }

    /**
     * Resolves a list of types to their FQDN.
     */
    public function resolveTypes(array $types, ReflectionProperty $property): array
    {
        $t = [];
        foreach ($types as $type) {
            $t[] = $this->resolveType($type, $property);
        }

        return $t;
    }

    /**
     * Resolves a type to its FQDN.
     *
     * @return string
     *
     * @throws AnnotationException
     */
    private function resolveType(string $type, ReflectionProperty $property)
    {
        $appendArray = strpos($type, '[]') !== false;
        $type = str_replace('[]', '', $type);

        if (\in_array($type, $this->builtInTypes)) {
            if ($appendArray) {
                $type .= '[]';
            }

            return $type;
        }

        $class = $property->getDeclaringClass();

        // If the class name is not fully qualified (i.e. doesn't start with a \)
        if ($type[0] !== '\\') {
            // Try to resolve the FQN using the class context
            $resolvedType = $this->tryResolveFqn($type, $class, $property);

            if (!$resolvedType) {
                throw new AnnotationException(sprintf('The @var annotation on %s::%s contains a non existent class "%s". '.'Did you maybe forget to add a "use" statement for this annotation?', $class->name, $property->getName(), $type));
            }

            $type = $resolvedType;
        }

        if (!$this->classExists($type)) {
            throw new AnnotationException(sprintf('The @var annotation on %s::%s contains a non existent class "%s"', $class->name, $property->getName(), $type));
        }

        // Remove the leading \ (FQN shouldn't contain it)
        $type = ltrim($type, '\\');

        if ($appendArray) {
            $type .= '[]';
        }

        return $type;
    }

    /**
     * Attempts to resolve the FQN of the provided $type based on the $class and $member context.
     *
     * @param string $type
     *
     * @return string|null Fully qualified name of the type, or null if it could not be resolved
     */
    private function tryResolveFqn($type, ReflectionClass $class, Reflector $member)
    {
        $alias = false === ($pos = strpos($type, '\\')) ? $type : substr($type, 0, $pos);
        $loweredAlias = strtolower($alias);

        // Retrieve "use" statements
        $uses = $this->parser->parseUseStatements($class);

        if (isset($uses[$loweredAlias])) {
            // Imported classes
            if ($pos !== false) {
                return $uses[$loweredAlias].substr($type, $pos);
            } else {
                return $uses[$loweredAlias];
            }
        } elseif ($this->classExists($class->getNamespaceName().'\\'.$type)) {
            return $class->getNamespaceName().'\\'.$type;
        } elseif (isset($uses['__NAMESPACE__']) && $this->classExists($uses['__NAMESPACE__'].'\\'.$type)) {
            // Class namespace
            return $uses['__NAMESPACE__'].'\\'.$type;
        } elseif ($this->classExists($type)) {
            // No namespace
            return $type;
        }

        if (version_compare(\PHP_VERSION, '5.4.0', '<')) {
            return null;
        } else {
            // If all fail, try resolving through related traits
            return $this->tryResolveFqnInTraits($type, $class, $member);
        }
    }

    /**
     * Attempts to resolve the FQN of the provided $type based on the $class and $member context, specifically searching
     * through the traits that are used by the provided $class.
     *
     * @param string $type
     *
     * @return string|null Fully qualified name of the type, or null if it could not be resolved
     */
    private function tryResolveFqnInTraits($type, ReflectionClass $class, Reflector $member)
    {
        /** @var ReflectionClass[] $traits */
        $traits = [];

        // Get traits for the class and its parents
        while ($class) {
            $traits = array_merge($traits, $class->getTraits());
            $class = $class->getParentClass();
        }

        foreach ($traits as $trait) {
            // Eliminate traits that don't have the property/method/parameter
            if ($member instanceof ReflectionProperty && !$trait->hasProperty($member->name)) {
                continue;
            } elseif ($member instanceof ReflectionMethod && !$trait->hasMethod($member->name)) {
                continue;
            } elseif ($member instanceof ReflectionParameter && !$trait->hasMethod($member->getDeclaringFunction()->name)) {
                continue;
            }

            // Run the resolver again with the ReflectionClass instance for the trait
            $resolvedType = $this->tryResolveFqn($type, $trait, $member);

            if ($resolvedType) {
                return $resolvedType;
            }
        }

        return null;
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    private function classExists($class)
    {
        return class_exists($class) || interface_exists($class);
    }

    // TYPE ANNOTATION DETECTION //

    /**
     * Detects the types from the PHP Doc Comment.
     *
     * @return string[]
     */
    private function detectFromPhpDocComment(ReflectionProperty $property): array
    {
        if (preg_match('/@var\s+([^\s]+)/', $property->getDocComment(), $matches)) {
            [, $typeString] = $matches;
            $types = explode('|', $typeString);

            return $this->resolveTypes($types, $property);
        }

        return [];
    }

    /**
     * Detects from a potential getter.
     *
     * @return string[]
     */
    private function detectFromGetters(ReflectionProperty $property): array
    {
        // Using Getters.
        $class = $property->getDeclaringClass();
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            // get only methods that have 0 parameter and start with 'get'
            if ($method->getNumberOfParameters() === 0 &&
                strpos($method->getName(), 'get') !== false &&
                stripos($method->getName(), $property->getName()) !== false) {
                return [(string) $method->getReturnType()];
            }
        }

        return [];
    }

    /**
     * Returns the type of a property through reflection on PHP 7.4 and onwards.
     *
     * @return string[]
     */
    private function detectFromReflection(ReflectionProperty $property): array
    {
        $php74OrGreater = \PHP_VERSION_ID >= 70400;
        if ($php74OrGreater) {
            /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
            $propertyType = $property->getType();
            if ($propertyType) {
                return [(string) $propertyType];
            }
        }

        return [];
    }
}
