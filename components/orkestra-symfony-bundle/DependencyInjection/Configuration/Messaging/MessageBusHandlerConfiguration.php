<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging;

/**
 * @internal
 */
class MessageBusHandlerConfiguration
{
    public string $serviceId;

    public ?string $className;

    public bool $autoroute;

    public function __construct(string $serviceId, ?string $className, bool $autoRouted)
    {
        $this->serviceId = $serviceId;
        $this->className = $className;
        $this->autoroute = $autoRouted;
    }
}
