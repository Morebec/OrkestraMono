<?php

namespace Morebec\Orkestra\Messaging\Context;

/**
 * The Context Provider is responsible for providing the current Message Bus Context.
 * It can be accessed in services depending on the context such as Message Handlers.
 */
interface MessageBusContextProviderInterface
{
    /**
     * Returns the current Message Bus Context or nullif there is none.
     */
    public function getContext(): ?MessageBusContext;

    /**
     * Method indicating if there is a correlation context at the moment.
     */
    public function hasContext(): bool;
}
