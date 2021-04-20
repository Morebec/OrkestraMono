<?php

namespace Morebec\Orkestra\Messaging\Validation;

use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Represents services responsible for Validating Messages before they are handled.
 */
interface MessageValidatorInterface
{
    /**
     * Validates a {@link MessageInterface} with given {@link MessageHeaders}.
     */
    public function validate(MessageInterface $message, MessageHeaders $headers): MessageValidationErrorList;

    /**
     * Indicates if this Validator can validate a given {@link MessageInterface} with  {@link MessageHeaders}.
     */
    public function supports(MessageInterface $message, MessageHeaders $headers): bool;
}
