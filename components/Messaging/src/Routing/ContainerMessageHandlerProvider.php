<?php

namespace Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\MessageHandlerInterface;
use Psr\Container\ContainerInterface;

/**
 * Implementation of a {@link MessageHandlerProviderInterface} fetching the {@link MessageHandlerInterface}
 * from a PSR-4 Container.
 */
class ContainerMessageHandlerProvider implements MessageHandlerProviderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getMessageHandler(string $messageHandlerClassName): ?MessageHandlerInterface
    {
        if (!$this->container->has($messageHandlerClassName)) {
            return null;
        }

        return $this->container->get($messageHandlerClassName);
    }
}
