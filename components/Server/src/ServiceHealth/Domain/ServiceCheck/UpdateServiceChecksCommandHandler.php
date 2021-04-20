<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck;

use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandHandlerInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceRepositoryInterface;

class UpdateServiceChecksCommandHandler implements DomainCommandHandlerInterface
{
    /**
     * @var ServiceRepositoryInterface
     */
    private $serviceRepository;

    public function __construct(ServiceRepositoryInterface $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function __invoke(UpdateServiceChecksCommand $command): void
    {
        $service = $this->serviceRepository->findById(ServiceId::fromString($command->serviceId));

        $checks = array_map(static function (ServiceCheckCommandDto $dto) {
            return new ServiceCheck(
                ServiceCheckId::fromString($dto->id),
                $dto->name,
                $dto->description,
                ServiceCheckUrl::fromString($dto->url),
                ServiceCheckInterval::fromInt($dto->interval),
                ServiceCheckThreshold::fromInt($dto->failureThreshold ?: 1),
                ServiceCheckThreshold::fromInt($dto->degradationThreshold ?: 1),
                ServiceCheckThreshold::fromInt($dto->successThreshold ?: 1),
                ServiceCheckTimeout::fromInt($dto->timeout),
                $dto->enabled ?: true
            );
        }, $command->serviceChecks);

        $service->replaceServiceChecks($checks);

        $this->serviceRepository->update($service);
    }
}
