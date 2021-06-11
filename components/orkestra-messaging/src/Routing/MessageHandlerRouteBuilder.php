<?php

namespace Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Inspects a {@link MessageHandlerInterface} through Reflection and extracts
 * the {@link MessageRouteInterface} it can support.
 */
class MessageHandlerRouteBuilder
{
    /**
     * @var string
     */
    private $messageHandlerClassName;

    /**
     * @var array
     */
    private $disabledMethods;

    public function __construct(string $messageHandlerClassName)
    {
        $this->messageHandlerClassName = $messageHandlerClassName;
        $this->disabledMethods = [];
    }

    public static function forMessageHandler(string $messageHandlerClassName): self
    {
        return new self($messageHandlerClassName);
    }

    /**
     * @return static
     */
    public function withMethodDisabled(string $methodName): self
    {
        $this->disabledMethods[$methodName] = $methodName;

        return $this;
    }

    /**
     * Builds the routes according to the definition.
     *
     * @throws ReflectionException
     */
    public function build(): MessageRouteCollection
    {
        $routes = [];
        $reflectionClass = new ReflectionClass($this->messageHandlerClassName);
        $methods = ($reflectionClass)->getMethods();
        foreach ($methods as $method) {
            if (\in_array($method->getName(), $this->disabledMethods)) {
                continue;
            }

            if (!$method->isPublic()) {
                continue;
            }

            $params = $method->getParameters();
            $nbParameters = $method->getNumberOfRequiredParameters();
            if ($nbParameters !== 1 && $nbParameters !== 2) {
                continue;
            }

            $eventClass = $params[0];
            $eventClassOb = $eventClass->getClass();
            if (!$eventClassOb) {
                continue;
            }

            $eventClassName = $eventClassOb->getName();
            if (!is_subclass_of($eventClassName, MessageInterface::class, true)) {
                continue;
            }

            if ($nbParameters === 2) {
                $secondArgClass = $params[1];
                $secondArgClassName = $secondArgClass->getClass()->getName();
                if (!is_subclass_of($secondArgClassName, MessageHeaders::class, true)) {
                    continue;
                }
            }

            $routes[] = new MessageRoute($eventClassName::getTypeName(), $reflectionClass->getName(), $method->getName());
        }

        return new MessageRouteCollection($routes);
    }
}
