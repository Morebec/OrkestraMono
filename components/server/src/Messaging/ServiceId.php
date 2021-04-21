<?php

namespace Morebec\Orkestra\OrkestraServer\Messaging;

use Morebec\Orkestra\OrkestraServer\Core\Modeling\AbstractEntityId;

class ServiceId extends AbstractEntityId
{
    public function __construct(string $value)
    {
        if (!$value) {
            throw new \InvalidArgumentException('A service ID cannot be blank');
        }
        parent::__construct($value);
    }
}
