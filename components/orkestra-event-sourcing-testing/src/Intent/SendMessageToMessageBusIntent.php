<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Intent;

use Morebec\Orkestra\EventSourcing\Testing\TestStage;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

class SendMessageToMessageBusIntent implements TestStageIntentInterface
{
    private MessageInterface $message;
    private ?MessageHeaders $messageHeaders;
    private MessageBusInterface $messageBus;

    public function __construct(
        MessageBusInterface $messageBus,
        MessageInterface $message,
        ?MessageHeaders $messageHeaders = null
    ) {
        $this->message = $message;
        $this->messageHeaders = $messageHeaders;
        $this->messageBus = $messageBus;
    }

    public function run(TestStage $stage): TestStageIntentExecutionResultInterface
    {
        $messageHeaders = $this->messageHeaders ?? new MessageHeaders();

        $response = $this->messageBus->sendMessage($this->message, $messageHeaders);

        return new MessageBusDispatchExecutionResult($stage, $this->message, $messageHeaders, $response);
    }
}
