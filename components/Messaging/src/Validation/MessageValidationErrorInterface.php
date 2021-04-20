<?php

namespace Morebec\Orkestra\Messaging\Validation;

interface MessageValidationErrorInterface
{
    /**
     * Returns the message explaining the reason for a given value for being invalid.
     */
    public function getMessage(): string;

    /**
     * Returns the property that was invalid.
     */
    public function property(): string;

    /**
     * Returns the value that was invalid.
     *
     * @return mixed
     */
    public function getValue();
}
