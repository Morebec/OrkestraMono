<?php

namespace Morebec\Orkestra\EventSourcing\Projection;

use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;

/**
 * Projectors are responsible for projecting events of write models into read models.
 * It follows a projection metaphor in cinemas: A Projector is a tool used to transform movie rolls
 * into pictures on a screen frame by frame.
 */
interface ProjectorInterface
{
    /**
     * Boots this projector so it is ready.
     */
    public function boot(): void;

    /**
     * Projects an event in a given context.
     */
    public function project(RecordedEventDescriptor $descriptor): void;

    /**
     * Gracefully shuts down the projector.
     */
    public function shutdown(): void;

    /**
     * Resets the projector's data to a clean slate.
     */
    public function reset(): void;

    /**
     * Returns the type name of the projector.
     * This allows referencing projectors with their name.
     */
    public static function getTypeName(): string;
}
