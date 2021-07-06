<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcasterChain;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class EventStoreConfigurationProcessor
{
    public function process(
        OrkestraConfiguration $orkestraConfiguration,
        EventStoreConfiguration $configuration
    ): void {
        try {
            $eventStoreService = $orkestraConfiguration->container()->services()->get(EventStoreInterface::class);
        } catch (ServiceNotFoundException $exception) {
            $eventStoreService = $orkestraConfiguration->service($configuration->implementationClassName);
            $eventStoreService->alias(EventStoreInterface::class, $configuration->implementationClassName);
        }

        // Decorators
        foreach ($configuration->decorators as $index => $decoratorClassName) {
            $orkestraConfiguration->service($decoratorClassName)
                ->decorate(EventStoreInterface::class, null, $index)
                ->args([service('.inner')]);
        }

        // Subscribers
        foreach ($configuration->subscribers as $subscriberClassName => $streamId) {
            $orkestraConfiguration->service($subscriberClassName);
            $eventStoreService->call('subscribeToStream', [
               EventStreamId::fromString($streamId),
               service($subscriberClassName),
            ]);
        }

        // Upcasters
        try {
            $upcasterChainService = $orkestraConfiguration->container()->services()->get(UpcasterChain::class);
        } catch (ServiceNotFoundException $exception) {
            $upcasterChainService = $orkestraConfiguration->service(UpcasterChain::class)->args([[]]);
        }

        foreach ($configuration->upcasters as $upcaster) {
            try {
                $orkestraConfiguration->container()->services()->get($upcaster);
            } catch (ServiceNotFoundException $exception) {
                $orkestraConfiguration->service($upcaster);
            }
            $upcasterChainService->call('addUpcaster', [service($upcaster)]);
        }
    }
}
