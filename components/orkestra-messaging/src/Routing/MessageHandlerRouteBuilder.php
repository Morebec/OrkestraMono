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
    private string $messageHandlerClassName;

    private array $disabledMethods;

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
            if (\in_array($method->getName(), $this->disabledMethods, true)) {
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

            $firstParameter = $params[0];
            $messageClass = $firstParameter->getClass();
            if (!$messageClass) {
                continue;
            }

            $messageClassName = $messageClass->getName();
            if ($messageClassName !== MessageInterface::class && !is_subclass_of($messageClassName, MessageInterface::class, true)) {
                continue;
            }

            if ($nbParameters === 2) {
                $secondParameter = $params[1];
                $secondParameterClassName = $secondParameter->getClass()->getName();
                if ($secondParameterClassName !== MessageHeaders::class && !is_subclass_of($secondParameterClassName, MessageHeaders::class, true)) {
                    continue;
                }
            }

            $messageTypeName = $messageClass->getMethod('getTypeName')->isAbstract() ? $messageClassName : $messageClassName::getTypeName();
            $routes[] = new MessageRoute(
                $messageTypeName,
                $reflectionClass->getName(),
                $method->getName()
            );
        }

        return new MessageRouteCollection($routes);
    }
}
