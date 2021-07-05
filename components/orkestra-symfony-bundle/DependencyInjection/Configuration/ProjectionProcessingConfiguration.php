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
        $this->configureProjectionGroup(new ProjectorGroupConfiguration(self::DEFAULT_GROUP_NAME));
    }

    public function configureProjectionGroup(ProjectorGroupConfiguration $configuration): self
    {
        $this->projectorGroups[$configuration->groupName] = $configuration;

        return $this;
    }
}
