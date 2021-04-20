<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck;

class ServiceCheckCommandDto
{
    /** @var string */
    public $id;

    /**
     * (optional).
     *
     * @var string|null
     */
    public $name;

    /**
     * (optional).
     *
     * @var string|null
     */
    public $description;

    /** @var int in milliseconds */
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

    /** @var string */
    public $enabled;
}
