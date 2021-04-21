<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

/**
 * Events coming out of a an application model can be used as an integration model for other systems,
 * to trigger side effects to the same system from which these events originated or simply, to compute read models
 * based on these events.
 *
 * They can be dispatched synchronously or to a queue in an asynchronous manner.
 * The event processor is a service responsible for handling an event out of the event store
 * and delegate it to the required components of the system.
 *
 * In essence, the EventProcessors are responsible for handling the technical side of processing events once
 * they come out of the event store: Whether this happens in real time (subscriptions), or by moving up the history of events (tracking) is
 * up to the processor the determine.
 *
 * In most event sourced systems, usually, there are two major types of EventProcessors:
 * - EventPublishers: that will publish the events to the registered event handlers for side effects or to a queue for integration with other systems.
 * - EventDenormalizer: that will provide the events to the different projectors to build read models.
 *
 * NOTE: Although the naming resembles the Axon Framework, there are many differences with it regarding these services.
 */
interface EventProcessorInterface
{
    /**
     * Returns the name of this event processor.
     * This name should always be unique to avoid collisions between multiple event processor running at the same
     * time and sharing resources.
     */
    public function getName(): string;

    /**
     * Starts this event processor so it can do its work on events.
     * Can be blocking until all the necessary events have been processed.
     */
    public function start(): void;

    /**
     * Shuts down this event processor gracefully, can be blocking until the
     * processor is considered stopped.
     */
    public function stop(): void;

    /**
     * Indicates if this event processor is running, that is
     * currently processing events.
     */
    public function isRunning(): bool;
}
