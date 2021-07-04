<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection;

use Morebec\Orkestra\EventSourcing\Projection\ProjectorGroup;

class ProjectorGroupRegistry
{
    /** @var ProjectorGroup[] */
    private $projectorGroups;

    public function __construct(iterable $projectorGroups = [])
    {
        $this->projectorGroups = [];
        foreach ($projectorGroups as $projectorGroup) {
            $this->addProjectorGroup($projectorGroup);
        }
    }

    public function addProjectorGroup(ProjectorGroup $projectorGroup): void
    {
        $this->projectorGroups[$projectorGroup->getName()] = $projectorGroup;
    }

    /**
     * Returns a Projector Group by its name or throws an exception if not found.
     */
    public function getProjectorGroup(string $name): ProjectorGroup
    {
        if (!\array_key_exists($name, $this->projectorGroups)) {
            throw new \RuntimeException("Projector Group \"$name\" not found in registry.");
        }

        return $this->projectorGroups[$name];
    }
}
