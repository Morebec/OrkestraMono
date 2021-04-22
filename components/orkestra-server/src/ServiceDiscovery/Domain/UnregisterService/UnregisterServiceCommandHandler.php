<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\UnregisterService;

use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandHandlerInterface;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceId;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceRegistryInterface;

class UnregisterServiceCommandHandler implements DomainCommandHandlerInterface
{
    /**
     * @var ServiceRegistryInterface
     */
    private $serviceRegistry;

    public function __construct(ServiceRegistryInterface $serviceRegistry)
    {
        $this->serviceRegistry = $serviceRegistry;
    }

    public function __invoke(UnregisterServiceCommand $command): void
    {
        $service = $this->serviceRegistry->findById(ServiceId::fromString($command->serviceId));

        $service->unregister();
    }
}
