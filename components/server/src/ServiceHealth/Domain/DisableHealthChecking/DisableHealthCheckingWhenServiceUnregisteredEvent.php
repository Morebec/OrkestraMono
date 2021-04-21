<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\DisableHealthChecking;

use Morebec\Orkestra\Messaging\Domain\Event\DomainEventHandlerInterface;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\UnregisterService\ServiceUnregisteredEvent;
use Morebec\Orkestra\Retry\RetryStrategy;

class DisableHealthCheckingWhenServiceUnregisteredEvent implements DomainEventHandlerInterface
{
    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(ServiceUnregisteredEvent $event): void
    {
        $sendCommand = static function () use ($event) {
            $response = $this->messageBus->sendMessage(new DisableServiceHealthCheckingCommand($event->serviceId));
            if ($response->isFailure()) {
                throw $response->getPayload();
            }
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
