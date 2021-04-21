<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\EnableHealthChecking;

use Morebec\Orkestra\Messaging\Domain\Event\DomainEventHandlerInterface;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\RegisterService\ServiceRegisteredEvent;
use Morebec\Orkestra\Retry\RetryStrategy;

/**
 * First enables health checking when a service is registered for the first time with the system.
 */
class EnableHealthCheckingWhenServiceRegisteredEventHandler implements DomainEventHandlerInterface
{
    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(ServiceRegisteredEvent $event): void
    {
        if ($event->wasAlreadyRegistered) {
            return;
        }

        $sendCommand = function () use ($event) {
            $this->messageBus->sendMessage(new EnableServiceHealthCheckingCommand($event->serviceId));
        };

        $sendCommand->bindTo($this);

        try {
            RetryStrategy::create()
                ->maximumAttempts(5)
                ->useExponentialBackoff(50)
                ->execute($sendCommand)
            ;
        } catch (\Throwable $throwable) {
            // TODO Notify Admin of problem.
            throw $throwable;
        }
    }
}
