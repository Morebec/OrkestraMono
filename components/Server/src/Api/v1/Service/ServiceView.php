<?php

namespace Morebec\Orkestra\OrkestraServer\Api\v1\Service;

use Morebec\Orkestra\DateTime\DateTime;

class ServiceView
{
    /** @var string */
    public $id;

    /** @var string|null */
    public $name;

    /** @var string|null */
    public $description;

    /** @var string|null */
    public $url;

    /** @var DateTime|null */
    public $lastRegisteredAt;

    /** @var int */
    public $nbRegistrations = 0;

    /** @var string */
    public $status = 'UNHEALTHY';

    /** @var ServiceCheckView[] */
    public $serviceChecks = [];
    /**
     * @var array
     */
    public $handledMessages = [];
    /**
     * @var array
     */
    public $metadata = [];

    /**
     * @var bool
     */
    public $healthCheckingEnabled = false;

    /** @var int of the data */
    public $version = -1;
}
