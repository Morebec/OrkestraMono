<?php

namespace Morebec\Orkestra\Messaging\Context;

/**
 * The Context Stack is used to allow nested message bus contexts for message handlers calling other message handlers
 * synchronously as part of their work.
 * TODO: tests.
 */
class MessageBusContextStack
{
    /**
     * @var MessageBusContext[]
     */
    private $data;

    public function __construct()
    {
        $this->data = [];
    }

    /**
     * Pushes a context on top of this stack.
     */
    public function push(MessageBusContext $context): void
    {
        $this->data[] = $context;
    }

    /**
     * Removes the element on the top of the stack.
     */
    public function pop(): MessageBusContext
    {
        $ctx = array_pop($this->data);
        if (!$ctx) {
            throw new \LogicException('Cannot pop Message Bus Context Stack: No context left on stack');
        }

        return $ctx;
    }

    /**
     * Returns the element at the top of the stack.
     */
    public function peek(): ?MessageBusContext
    {
        $nbContexts = \count($this->data);

        if ($nbContexts === 0) {
            return null;
        }

        return $this->data[$nbContexts - 1];
    }
}
