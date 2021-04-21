<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\EnableHealthChecking;

use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandHandlerInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\Service;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceNotFoundException;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceRepositoryInterface;

class EnableServiceHealthCheckingCommandHandler implements DomainCommandHandlerInterface
{
    /**
     * @var ServiceRepositoryInterface
     */
    private $serviceRepository;

    public function __construct(ServiceRepositoryInterface $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function __invoke(EnableServiceHealthCheckingCommand $command): void
    {
        $serviceId = ServiceId::fromString($command->serviceId);
        try {
            $service = $this->serviceRepository->findById($serviceId);
            $serviceWasCreated = false;
        } catch (ServiceNotFoundException $exception) {
            $service = Service::create($serviceId);
            $serviceWasCreated = true;
        }

        $service->enable();

        if ($serviceWasCreated) {
            $this->serviceRepository->add($service);
        } else {
            $this->serviceRepository->update($service);
        }
    }
}
