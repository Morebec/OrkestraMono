<?php

namespace Morebec\Orkestra\OrkestraServer\Api\v1\ServiceCheck;

use Morebec\Orkestra\DateTime\DateTime;

class HealthCheckView
{
    /** @var string */
    public $id;

    /** @var string */
    public $serviceId;

    /** @var string */
    public $serviceCheckId;

    /** @var string */
    public $url;

    /** @var DateTime */
    public $startedAt;

    /** @var DateTime|null */
    public $endedAt = null;

    /** @var HealthCheckResponseView|null */
    public $response = null;

    /** @var string|null */
    public $status = null;

    /**
     * @var bool
     */
    public $timeout = false;
}
