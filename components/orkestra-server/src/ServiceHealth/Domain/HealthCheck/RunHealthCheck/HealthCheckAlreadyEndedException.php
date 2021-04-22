<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\RunHealthCheck;

use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\HealthCheckId;
use Throwable;

class HealthCheckAlreadyEndedException extends \RuntimeException
{
    public function __construct(HealthCheckId $id, Throwable $previous = null)
    {
        parent::__construct(sprintf('Health check "%s" has already ended.', $id), 0, $previous);
    }
}
