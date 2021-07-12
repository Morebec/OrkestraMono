<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging;

/**
 * @internal
 */
class MessageBusHandlerConfiguration
{
    public string $serviceId;

    public ?string $className;

    public function __construct(string $serviceId, ?string $className)
    {
        $this->serviceId = $serviceId;
        $this->className = $className;
    }
}
