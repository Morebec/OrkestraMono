<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\BlacklistService;

use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandHandlerInterface;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceId;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceRegistryInterface;

class BlacklistServiceCommandHandler implements DomainCommandHandlerInterface
{
    /**
     * @var ServiceRegistryInterface
     */
    private $serviceRegistry;

    public function __construct(ServiceRegistryInterface $serviceRegistry)
    {
        $this->serviceRegistry = $serviceRegistry;
    }

    public function __invoke(BlacklistServiceCommand $command): void
    {
        $service = $this->serviceRegistry->findById(ServiceId::fromString($command->serviceId));

        $service->blacklist();

        $this->serviceRegistry->update($service);
    }
}
