<?php

namespace Morebec\Orkestra\Messaging\Validation;

/**
 * Simple default implementation of {@link MessageValidationErrorInterface}.
 */
class MessageValidationError implements MessageValidationErrorInterface
{
    /**
     * @var string
     */
    private $message;
    /**
     * @var string
     */
    private $property;
    private $value;

    public function __construct(string $message, string $property, $value)
    {
        $this->message = $message;
        $this->property = $property;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function property(): string
    {
        return $this->property;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }
}
