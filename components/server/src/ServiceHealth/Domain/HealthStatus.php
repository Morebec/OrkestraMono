<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain;

use Morebec\Orkestra\Enum\Enum;

/**
 * @method static self HEALTHY()
 * @method static self UNHEALTHY()
 * @method static self DEGRADED()
 */
class HealthStatus extends Enum
{
    public const HEALTHY = 'HEALTHY';

    public const UNHEALTHY = 'UNHEALTHY';

    public const DEGRADED = 'DEGRADED';
}
