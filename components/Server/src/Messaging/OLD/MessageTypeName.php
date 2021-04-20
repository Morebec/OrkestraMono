<?php

namespace Morebec\Orkestra\OrkestraServer\Messaging;

class MessageTypeName
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        if (!$value) {
            throw new \InvalidArgumentException('A Message must have a type name');
        }
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
