<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\DisableHealthChecking;

use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandHandlerInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceRepositoryInterface;

class DisableServiceHealthCheckingCommandHandler implements DomainCommandHandlerInterface
{
    /**
     * @var ServiceRepositoryInterface
     */
    private $serviceRepository;

    public function __construct(ServiceRepositoryInterface $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function __invoke(DisableServiceHealthCheckingCommand $command): void
    {
        $serviceId = ServiceId::fromString($command->serviceId);

        $service = $this->serviceRepository->findById($serviceId);

        $service->disable();

        $this->serviceRepository->update($service);
    }
}
