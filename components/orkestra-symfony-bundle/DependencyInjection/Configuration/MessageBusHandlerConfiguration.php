<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

/**
 * @internal
 */
class MessageBusHandlerConfiguration
{
    /** @var string */
    public $serviceId;

    /** @var string */
    public $className;

    /** @var bool */
    public $autoroute;

    public function __construct(string $serviceId, string $className, bool $autoRouted)
    {
        $this->serviceId = $serviceId;
        $this->className = $className;
        $this->autoroute = $autoRouted;
    }
}
