<?php

namespace Morebec\Orkestra\Messaging\Timer;

use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Retry\NoRetryStrategy;
use Morebec\Orkestra\Retry\RetryStrategy;

/**
 * Implementation of a TimerPublisher that publishes the timers on the message bus.
 * It contains allows a retry strategy to publish the timers again on the message bus if there are errors.
 * To disable any retry strategy simply pass null.
 */
class MessageBusTimerPublisher implements TimerPublisherInterface
{
    /**
     * @var MessageBusInterface
     */
    protected $messageBus;
    /**
     * @var RetryStrategy|null
     */
    private $retryStrategy;

    public function __construct(MessageBusInterface $messageBus, ?RetryStrategy $retryStrategy = null)
    {
        $this->messageBus = $messageBus;

        if (!$retryStrategy) {
            $retryStrategy = new NoRetryStrategy();
        }

        $this->retryStrategy = $retryStrategy;
    }

    public function publish(TimerInterface $timer, MessageHeaders $headers): void
    {
        $messageBus = $this->messageBus;
        try {
            $this->retryStrategy->execute(static function () use ($messageBus, $timer, $headers) {
                $response = $messageBus->sendMessage($timer, $headers);
                if ($response->isFailure()) {
                    throw $response->getPayload();
                }
            });
        } catch (\Throwable $throwable) {
            // Swallow the last throwable, so the processor does not choke and continue with its work with the other timers.
            echo $throwable->getMessage();
        }
    }
}
