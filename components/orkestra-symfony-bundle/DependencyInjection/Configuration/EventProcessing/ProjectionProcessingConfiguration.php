<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventProcessing;

use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\NotConfiguredException;

/**
 * @internal
 */
class ProjectionProcessingConfiguration
{
    public const DEFAULT_GROUP_NAME = 'default';

    /** @var ProjectorGroupConfiguration[] */
    public array $projectorGroups;

    public function __construct()
    {
        $this->projectorGroups = [];

        // Add a default group.
        $this->configureProjectorGroup(new ProjectorGroupConfiguration(self::DEFAULT_GROUP_NAME));
    }

    public function configureProjectorGroup(ProjectorGroupConfiguration $configuration): self
    {
        $this->projectorGroups[$configuration->groupName] = $configuration;

        return $this;
    }

    /**
     * Returns a previously configured projectorGroup or throws an exception if not found.
     */
    public function projectorGroup(string $groupName): ProjectorGroupConfiguration
    {
        $group = $this->projectorGroups[$groupName] ?? null;
        if (!$group) {
            throw new NotConfiguredException("Projector Group \"$groupName\" not configured.");
        }

        return $group;
    }
}
