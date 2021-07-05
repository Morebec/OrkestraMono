<?php

namespace Morebec\Orkestra\Messaging\Timeout;

use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Retry\NoRetryStrategy;
use Morebec\Orkestra\Retry\RetryStrategyInterface;

/**
 * Implementation of a TimeoutPublisher that publishes the timeouts on the message bus.
 * It contains allows a orkestra-retry strategy to publish the timeouts again on the message bus if there are errors.
 * To disable any orkestra-retry strategy simply pass null.
 */
class MessageBusTimeoutPublisher implements TimeoutPublisherInterface
{
    protected MessageBusInterface $messageBus;

    private ?RetryStrategyInterface $retryStrategy;

    public function __construct(MessageBusInterface $messageBus, ?RetryStrategyInterface $retryStrategy = null)
    {
        $this->messageBus = $messageBus;

        if (!$retryStrategy) {
            $retryStrategy = new NoRetryStrategy();
        }

        $this->retryStrategy = $retryStrategy;
    }

    public function publish(TimeoutInterface $timeout, MessageHeaders $headers): void
    {
        try {
            $this->retryStrategy->execute(function () use ($timeout, $headers) {
                $response = $this->messageBus->sendMessage($timeout, $headers);
                if ($response->isFailure()) {
                    throw $response->getPayload();
                }
            });
        } catch (\Throwable $throwable) {
            // Swallow the last throwable, so the processor does not choke and continue with its work with the other timeouts.
            echo $throwable->getMessage();
        }
    }
}
