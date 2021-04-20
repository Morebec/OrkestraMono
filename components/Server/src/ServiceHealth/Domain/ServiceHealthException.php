<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain;

use Throwable;

class ServiceHealthException extends \RuntimeException implements ServiceHealthExceptionInterface
{
    public function __construct($message, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
