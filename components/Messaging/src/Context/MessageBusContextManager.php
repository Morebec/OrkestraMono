<?php

namespace Morebec\Orkestra\Messaging\Context;

use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Default implementation of {@link MessageBusContextManagerInterface}, keeping the context in memory.
 */
class MessageBusContextManager implements MessageBusContextManagerInterface
{
    /**
     * @var MessageBusContextStack
     */
    private $contextStack;

    public function __construct(?MessageBusContext $previousContext = null)
    {
        $this->contextStack = new MessageBusContextStack();
        if ($previousContext) {
            $this->contextStack->push($previousContext);
        }
    }

    /**
     * Starts a new context for a given message with headers.
     */
    public function startContext(MessageInterface $message, MessageHeaders $headers): void
    {
        $currentContext = $this->contextStack->peek();
        if ($currentContext) {
            $headers->set(MessageHeaders::CORRELATION_ID, $currentContext->getCorrelationId());
            $headers->set(MessageHeaders::CAUSATION_ID, $currentContext->getMessageId());
        } else {
            if (!$headers->get(MessageHeaders::CORRELATION_ID)) {
                $headers->set(MessageHeaders::CORRELATION_ID, $headers->get(MessageHeaders::MESSAGE_ID));
            }

            if (!$headers->get(MessageHeaders::CAUSATION_ID)) {
                $headers->set(MessageHeaders::CAUSATION_ID, null);
            }
        }

        $this->contextStack->push(new MessageBusContext($message, $headers));
    }

    /**
     * Ends the currently active context or throw an exception if there was no started context.
     */
    public function endContext(): void
    {
        if (!$this->contextStack->peek()) {
            throw new NoMessageBusContextToEndException();
        }

        $this->contextStack->pop();
    }

    /**
     * Returns the current context or null if there is none.
     */
    public function getContext(): ?MessageBusContext
    {
        return $this->contextStack->peek();
    }
}
