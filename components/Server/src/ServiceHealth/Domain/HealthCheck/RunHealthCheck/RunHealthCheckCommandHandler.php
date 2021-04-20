<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\RunHealthCheck;

use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandHandlerInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\HealthCheck;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\HealthCheckRepositoryInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\HealthCheckRunnerInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceRepositoryInterface;

class RunHealthCheckCommandHandler implements DomainCommandHandlerInterface
{
    /**
     * @var ServiceRepositoryInterface
     */
    private $serviceRepository;

    /** @var HealthCheckRepositoryInterface */
    private $healthCheckRepository;

    /**
     * @var HealthCheckRunnerInterface
     */
    private $healthCheckRunner;

    /**
     * @var ClockInterface
     */
    private $clock;

    public function __construct(
        ServiceRepositoryInterface $serviceRepository,
        HealthCheckRepositoryInterface $healthCheckRepository,
        HealthCheckRunnerInterface $healthCheckRunner,
        ClockInterface $clock
    ) {
        $this->serviceRepository = $serviceRepository;
        $this->healthCheckRepository = $healthCheckRepository;
        $this->healthCheckRunner = $healthCheckRunner;
        $this->clock = $clock;
    }

    public function __invoke(RunHealthCheckCommand $command): void
    {
        $serviceId = ServiceId::fromString($command->serviceId);
        $service = $this->serviceRepository->findById($serviceId);

        $serviceCheckId = ServiceCheckId::fromString($command->healthCheckId);

        $serviceCheck = $service->getServiceCheckById($serviceCheckId);

        $healthCheck = HealthCheck::start(
            $serviceId,
            $serviceCheckId,
            $serviceCheck->getTimeout(),
            $serviceCheck->getUrl(),
            $this->clock->now()
        );

        $this->healthCheckRepository->add($healthCheck);

        // For version
        $healthCheck = $this->healthCheckRepository->findById($healthCheck->getId());

        $response = $this->healthCheckRunner->runCheck($healthCheck, $serviceCheck);

        $healthCheck->end($this->clock->now(), $response);

        $this->healthCheckRepository->update($healthCheck);
    }
}
