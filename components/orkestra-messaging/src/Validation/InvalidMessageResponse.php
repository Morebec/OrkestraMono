<?php

namespace Morebec\Orkestra\Messaging\Validation;

use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;

/**
 * Response returned when a Message was deemed Invalid.
 */
class InvalidMessageResponse implements MessageBusResponseInterface
{
    private MessageValidationErrorList $errors;

    public function __construct(MessageValidationErrorList $errors)
    {
        $this->errors = $errors;
    }

    public function getPayload(): MessageValidationErrorList
    {
        return $this->errors;
    }

    public function isSuccess(): bool
    {
        return false;
    }

    public function isFailure(): bool
    {
        return true;
    }

    public function getStatusCode(): MessageBusResponseStatusCode
    {
        return MessageBusResponseStatusCode::INVALID();
    }
}
