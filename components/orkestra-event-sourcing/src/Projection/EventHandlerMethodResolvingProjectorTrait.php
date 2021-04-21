<?php

namespace Morebec\Orkestra\EventSourcing\Projection;

use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;

/**
 * Utility trait that resolves the event handler methods based on a typed Event.
 * It is convention based where it only considers methods starting with "on" or "apply" and at most two arguments
 * where the first one is the Typed Event and the second one (optional) is the Recorded Event.
 * It is meant to be used with {@link AbstractDenormalizingProjector}.
 */
trait EventHandlerMethodResolvingProjectorTrait
{
    protected function projectEvent(?DomainEventInterface $event, RecordedEventDescriptor $eventDescriptor): void
    {
        $eventClass = \get_class($event);
        $self = new \ReflectionClass($this);

        $methods = $self->getMethods();
        foreach ($methods as $method) {
            $methodName = $method->getName();
            if (!str_starts_with($methodName, 'on') && !str_starts_with($methodName, 'apply')) {
                continue;
            }

            $parameters = $method->getParameters();
            $paramCount = $method->getNumberOfParameters();
            if ($paramCount === 0 || $paramCount > 2) {
                continue;
            }
            $firstParameterClass = $parameters[0]->getClass();
            if (!$firstParameterClass) {
                continue;
            }
            $firstParameterClassName = $firstParameterClass->getName();
            if ($firstParameterClassName !== $eventClass) {
                continue;
            }
            if ($paramCount === 2) {
                $secondParameterClass = $parameters[1]->getClass();
                if (!$secondParameterClass) {
                    continue;
                }

                $secondParameterClassName = $secondParameterClass->getName();
                if ($secondParameterClassName !== RecordedEventDescriptor::class) {
                    continue;
                }

                $this->{$methodName}($event, $eventDescriptor);
                continue;
            }
            $this->{$methodName}($event);
        }
    }
}
