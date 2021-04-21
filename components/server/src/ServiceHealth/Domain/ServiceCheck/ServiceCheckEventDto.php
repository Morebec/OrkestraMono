<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck;

class ServiceCheckEventDto
{
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var string|null */
    public $description;

    /** @var int in seconds */
    public $interval;

    /** @var int in seconds */
    public $timeout;

    /** @var int Represents the number of failures of this check that are tolerated before it is considered degraded. */
    public $degradationThreshold;

    /** @var int Represents the number of failures of this check that are tolerated before it is considered down. */
    public $failureThreshold;

    /** @var int Represents the number of success calls for this check before it can transition back to being healthy */
    public $successThreshold;

    /** @var string */
    public $url;

    /** @var bool */
    public $enabled;

    /** @var string */
    public $status;
}
