<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\Messaging\Timer\AbstractTimer;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceId;

class CheckHealthTimer extends AbstractTimer
{
    /**
     * @var string
     */
    public $serviceId;

    /**
     * @var string
     */
    public $serviceCheckId;

    public function __construct(ServiceId $serviceId, ServiceCheckId $serviceCheckId, DateTime $scheduledAt)
    {
        parent::__construct("$serviceCheckId@$serviceId", $scheduledAt);
        $this->serviceId = (string) $serviceId;
        $this->serviceCheckId = (string) $serviceCheckId;
    }

    public static function getTypeName(): string
    {
        return 'timer.check_health';
    }
}
