<?php

namespace Morebec\Orkestra\OrkestraServer\Api\v1\Service;

use Morebec\Orkestra\DateTime\DateTime;

class ServiceCheckView
{
    /** @var string */
    public $id;

    /** @var string */
    public $status;

    /** @var string */
    public $name;

    /** @var string|null */
    public $description;

    /** @var int */
    public $interval;

    /** @var int */
    public $degradationThreshold;

    /** @var int */
    public $failureThreshold;

    /** @var int */
    public $successThreshold;

    /** @var string */
    public $url;

    /** @var bool */
    public $enabled;

    /** @var int */
    public $timeout;

    /** @var DateTime|null */
    public $lastCheckedAt;
}
