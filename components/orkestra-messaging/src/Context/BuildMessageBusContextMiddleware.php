<?php

namespace Morebec\Orkestra\Messaging\Context;

use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandInterface;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\Messaging\Domain\Query\DomainQueryInterface;
use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareInterface;
use Ramsey\Uuid\Uuid;

/**
 * Middleware responsible for building the message bus context.
 */
class BuildMessageBusContextMiddleware implements MessageBusMiddlewareInterface
{
    /**
     * @var ClockInterface
     */
    private $clock;

    /**
     * @var MessageBusContextManagerInterface
     */
    private $contextManager;

    public function __construct(ClockInterface $clock, MessageBusContextManagerInterface $contextManager)
    {
        $this->clock = $clock;
        $this->contextManager = $contextManager;
    }

    public function __invoke(MessageInterface $message, MessageHeaders $headers, callable $next): MessageBusResponseInterface
    {
        // Build context
        $headers->set(MessageHeaders::MESSAGE_TYPE_NAME, $message::getTypeName());

        $headers->set(MessageHeaders::MESSAGE_TYPE, $this->detectMessageType($message));

        $headers->set(MessageHeaders::SENT_AT, $this->clock->now()->getMillisTimestamp());

        if (!$headers->get(MessageHeaders::MESSAGE_ID)) {
            $headers->set(MessageHeaders::MESSAGE_ID, (string) Uuid::uuid4());
        }

        $this->contextManager->startContext($message, $headers);

        $response = $next($message, $headers);

        // Unset context
        $this->contextManager->endContext();

        return $response;
    }

    /**
     * Detects the type of a message and returns it as a string.
     * (E.g. event, command, query). For non standard messages returns "generic".
     */
    protected function detectMessageType(MessageInterface $message): string
    {
        if ($message instanceof DomainEventInterface) {
            $messageType = 'event';
        } elseif ($message instanceof DomainCommandInterface) {
            $messageType = 'command';
        } elseif ($message instanceof DomainQueryInterface) {
            $messageType = 'query';
        } else {
            $messageType = 'generic';
        }

        return $messageType;
    }
}
