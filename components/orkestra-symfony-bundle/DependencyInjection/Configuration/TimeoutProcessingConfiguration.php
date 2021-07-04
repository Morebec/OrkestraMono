<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\Messaging\Timeout\TimeoutManager;

class TimeoutProcessingConfiguration
{
    /** @var string */
    public $managerImplementationClassName;

    /** @var string */
    public $storageImplementationClassName;

    public function usingManagerImplementation(string $className): self
    {
        $this->managerImplementationClassName = $className;

        return $this;
    }

    public function usingDefaultManagerImplementation(): self
    {
        return $this->usingManagerImplementation(TimeoutManager::class);
    }

    public function usingStorageImplementation(string $className): self
    {
        $this->storageImplementationClassName = $className;

        return $this;
    }
}
