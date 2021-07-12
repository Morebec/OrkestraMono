<?php

namespace Morebec\Orkestra\Messaging;

/**
 * Implementation of a MessageBus that performs nothing but collecting messages it received
 * and returns a predefined {@link MessageBusResponseInterface}.
 *
 * This is to be used mostly in tests.
 */
class FakeMessageBus implements MessageBusInterface
{
    private MessageBusResponseInterface $response;

    /** @var MessageInterface[] */
    private array $messages;

    public function __construct(?MessageBusResponseInterface $response = null)
    {
        if ($response === null) {
            /** @var MessageBusResponseInterface $r */
            $r = new class() extends AbstractMessageBusResponse {
                public function __construct()
                {
                    parent::__construct(MessageBusResponseStatusCode::SUCCEEDED());
                }
            };
            $response = $r;
        }

        /* @var MessageBusResponseInterface $response */
        $this->response = $response;

        $this->messages = [];
    }

    public function sendMessage(MessageInterface $message, ?MessageHeaders $headers = null): MessageBusResponseInterface
    {
        $this->messages[] = $message;

        return $this->response;
    }

    /**
     * Changes the response that will be returned.
     */
    public function changeResponse(MessageBusResponseInterface $response): void
    {
        $this->response = $response;
    }

    /**
     * @return MessageInterface[]
     */
    public function getCollectedMessages(): array
    {
        return $this->messages;
    }
}
