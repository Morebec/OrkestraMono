<?php

namespace Morebec\Orkestra\EventSourcing\Modeling;

use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;

/**
 * Utility trait that resolves the event handler methods based on a typed Event.
 * It is convention based where it only considers methods starting with "apply" and at most one argument
 * being the Typed Event.
 * It is meant to be used with {@link AbstractEventSourcedAggregateRoot} to avoid having to do if statements
 * in the {@link AbstractEventSourcedAggregateRoot::onDomainEvent()} method.
 */
trait EventSourcedAggregateRootTrait
{
    /**
     * Method called to apply a domain event to this aggregate's state.
     */
    protected function onDomainEvent(DomainEventInterface $event): void
    {
        $eventClass = \get_class($event);
        $self = new \ReflectionClass($this);

        $methods = $self->getMethods();
        foreach ($methods as $method) {
            $methodName = $method->getName();
            if (!str_starts_with($methodName, 'apply')) {
                continue;
            }

            $parameters = $method->getParameters();
            $paramCount = $method->getNumberOfParameters();

            if ($paramCount !== 1) {
                continue;
            }

            $parameterClass = $parameters[0]->getClass();
            if (!$parameterClass) {
                continue;
            }

            $parameterClassName = $parameterClass->getName();
            if ($parameterClassName !== $eventClass) {
                continue;
            }

            $this->{$methodName}($event);
        }
    }
}
