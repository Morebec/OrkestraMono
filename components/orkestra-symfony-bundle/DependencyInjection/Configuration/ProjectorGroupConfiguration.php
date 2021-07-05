<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

/**
 * Represents a configuration of ProjectorGroups for DI.
 */
class ProjectorGroupConfiguration
{
    /** @var string Default prefix used for the ID of the services in the DI container. */
    public const DEFAULT_PREFIX = 'projector_group_';

    public ?string $groupName;

    /** @var string[] */
    public array $projectors = [];

    public function __construct(?string $groupName = null)
    {
        if ($groupName) {
            $this->withName($groupName);
        }
    }

    /**
     * Configures the name of this group.
     *
     * @return $this
     */
    public function withName(string $groupName): self
    {
        $this->groupName = $groupName;

        return $this;
    }

    public function withProjector(string $projectorClassName): void
    {
        $this->projectors[] = $projectorClassName;
    }
}
