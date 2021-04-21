<?php

namespace Morebec\Orkestra\Messaging\Context;

/**
 * Default implementation of {@link MessageBusContextProviderInterface}.
 * TODO: tests.
 */
class MessageBusContextProvider implements MessageBusContextProviderInterface
{
    /**
     * @var MessageBusContextManagerInterface
     */
    private $contextManager;

    public function __construct(MessageBusContextManagerInterface $contextManager)
    {
        $this->contextManager = $contextManager;
    }

    /**
     * Returns the current Message Bus Context or nullif there is none.
     */
    public function getContext(): ?MessageBusContext
    {
        return $this->contextManager->getContext();
    }

    /**
     * Method indicating if there is a correlation context at the moment.
     */
    public function hasContext(): bool
    {
        return $this->getContext() !== null;
    }
}
