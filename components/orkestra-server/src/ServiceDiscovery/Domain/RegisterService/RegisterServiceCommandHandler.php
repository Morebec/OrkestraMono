<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\RegisterService;

use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandHandlerInterface;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\MessageTypeName;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\Service;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceId;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceMetadata;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceNotFoundException;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceRegistryInterface;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceUrl;

class RegisterServiceCommandHandler implements DomainCommandHandlerInterface
{
    /**
     * @var ServiceRegistryInterface
     */
    private $serviceRegistry;

    public function __construct(ServiceRegistryInterface $serviceRegistry)
    {
        $this->serviceRegistry = $serviceRegistry;
    }

    /**
     * Handles the given command and returns the latest version of the service.
     */
    public function __invoke(RegisterServiceCommand $command): void
    {
        $serviceId = ServiceId::fromString($command->serviceId);
        try {
            $service = $this->serviceRegistry->findById($serviceId);
            $serviceWasCreated = false;
        } catch (ServiceNotFoundException $exception) {
            $service = Service::initialize($serviceId);
            $serviceWasCreated = true;
        }

        $url = ServiceUrl::fromString($command->url);

        // Ensure type names are unique.
        $handledMessages = $command->handledMessages ?: [];

        $handledMessages = array_flip(array_keys($handledMessages));
        $handledMessages = array_map(static function (string $message) {
            return MessageTypeName::fromString($message);
        }, $handledMessages);

        $metadata = $command->metadata ?: [];
        $service->register(
            $url,
            $handledMessages,
            $command->name,
            $command->description ?: null,
            ServiceMetadata::fromArray($metadata)
        );

        if ($serviceWasCreated) {
            $this->serviceRegistry->add($service);
        } else {
            $this->serviceRegistry->update($service);
        }
    }
}
