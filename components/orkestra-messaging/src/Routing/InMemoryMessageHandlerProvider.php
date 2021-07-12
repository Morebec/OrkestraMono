<?php

namespace Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\MessageHandlerInterface;

/**
 * Implementation of a {@link MessageHandlerProviderInterface} that holds a list of message handlers in memory.
 */
class InMemoryMessageHandlerProvider implements MessageHandlerProviderInterface
{
    /**
     * @var MessageHandlerInterface[]
     */
    private array $handlers;

    public function __construct(iterable $handlers = [])
    {
        $this->handlers = [];

        foreach ($handlers as $handler) {
            $this->addMessageHandler($handler);
        }
    }

    /**
     * Adds a message handler to this provider.
     *
     * @return $this
     */
    public function addMessageHandler(MessageHandlerInterface $handler): self
    {
        $this->handlers[\get_class($handler)] = $handler;

        return $this;
    }

    public function getMessageHandler(string $messageHandlerClassName): ?MessageHandlerInterface
    {
        return $this->handlers[$messageHandlerClassName] ?? null;
    }
}
