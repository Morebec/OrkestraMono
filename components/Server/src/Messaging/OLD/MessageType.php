<?php

namespace Morebec\Orkestra\OrkestraServer\Messaging;

class MessageType
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        if (!$value) {
            throw new \InvalidArgumentException('A Message must have a type');
        }
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function EVENT(): self
    {
        return new self('EVENT');
    }

    public static function COMMAND(): self
    {
        return new self('COMMAND');
    }

    public static function QUERY(): self
    {
        return new self('QUERY');
    }
}
