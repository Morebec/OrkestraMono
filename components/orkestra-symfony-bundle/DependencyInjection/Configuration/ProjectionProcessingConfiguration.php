<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

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

    public function getProjectorGroupConfiguration(string $groupName): ProjectorGroupConfiguration
    {
        $group = $this->projectorGroups[$groupName] ?? null;
        if (!$group) {
            throw new \InvalidArgumentException('Projector Group not found. Did you configure it properly ?');
        }

        return $group;
    }
}
