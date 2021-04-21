<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck;

use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceId;

class ServiceCheckUpdatedEvent implements DomainEventInterface
{
    /** @var string */
    public $serviceId;

    /** @var string */
    public $serviceCheckId;

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

    public function __construct(ServiceId $serviceId, ServiceCheck $check)
    {
        $this->serviceId = (string) $serviceId;
        $this->serviceCheckId = (string) $check->getId();

        $this->status = (string) $check->getStatus();
        $this->name = $check->getName();
        $this->description = $check->getDescription();
        $this->interval = $check->getInterval()->toInt();
        $this->degradationThreshold = $check->getDegradationThreshold()->toInt();
        $this->failureThreshold = $check->getFailureThreshold()->toInt();
        $this->successThreshold = $check->getSuccessThreshold()->toInt();
        $this->url = (string) $check->getUrl();
        $this->enabled = $check->isEnabled();
        $this->timeout = $check->getTimeout()->toInt();
    }

    public static function getTypeName(): string
    {
        return 'service.check.updated';
    }
}
