<?php

namespace Morebec\Orkestra\EventSourcing\Projection;

use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;

/**
 * A ProjectorGroup allows to group multiple projectors together in a single cohesive unit so they can all be operated
 * as if they were one.
 *
 * It allows to ensure a given set of projectors either succeed together or fail together.
 *
 * This can be useful for example to create a complete Read Model of all the necessary aggregates in a RDBMS.
 * Where there could be one projector per table, since they all depend on each other, it would make sense to group them together.
 */
class ProjectorGroup implements ProjectorInterface
{
    public const TYPE_NAME = 'projection_group';
    /**
     * @var string
     */
    private $name;

    /**
     * @var ProjectorInterface[]
     */
    private $projectors;

    public function __construct(string $groupName, iterable $projectors)
    {
        if (!$groupName) {
            throw new \InvalidArgumentException('A Group Projector cannot have an empty name.');
        }

        $this->name = $groupName;

        $this->projectors = [];

        foreach ($projectors as $projector) {
            $this->addProjector($projector);
        }
    }

    public function boot(): void
    {
        foreach ($this->projectors as $projector) {
            $projector->boot();
        }
    }

    public function project(RecordedEventDescriptor $descriptor): void
    {
        foreach ($this->projectors as $projector) {
            $projector->project($descriptor);
        }
    }

    public function shutdown(): void
    {
        foreach ($this->projectors as $projector) {
            $projector->shutdown();
        }
    }

    public function reset(): void
    {
        foreach ($this->projectors as $projector) {
            $projector->reset();
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function getTypeName(): string
    {
        return self::TYPE_NAME;
    }

    /**
     * Adds a projector to this group.
     */
    public function addProjector(ProjectorInterface $projector): void
    {
        $this->projectors[] = $projector;
    }
}
