<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Infrastructure;

use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContextInterface;
use Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer\FluentDenormalizer;
use Morebec\Orkestra\Normalization\Normalizer\ObjectNormalizer\FluentNormalizer;
use Morebec\Orkestra\Normalization\ObjectNormalizerInterface;
use Morebec\Orkestra\OrkestraServer\Core\Persistence\PostgreSqlObjectStore;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthStatus;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheckId;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheckThresholdCounter;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheckThresholdCounterRepositoryInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceId;
use Morebec\Orkestra\PostgreSqlDocumentStore\PostgreSqlDocumentStore;

class PostgreSqlServiceCheckThresholdCounterRepository implements ServiceCheckThresholdCounterRepositoryInterface
{
    public const COLLECTION_NAME = 'service_check_threshold_counters';

    /**
     * @var PostgreSqlObjectStore
     */
    private $store;

    public function __construct(PostgreSqlDocumentStore $store, ObjectNormalizerInterface $normalizer)
    {
        $this->store = new PostgreSqlObjectStore($store, self::COLLECTION_NAME, ServiceCheckThresholdCounter::class, $normalizer);

        $normalizer->addNormalizer(FluentNormalizer::for(ServiceId::class)->asString());
        $normalizer->addDenormalizer(FluentDenormalizer::for(ServiceId::class)->as(static function (DenormalizationContextInterface $context) {
            return ServiceId::fromString($context->getValue());
        }));

        $normalizer->addNormalizer(FluentNormalizer::for(ServiceCheckId::class)->asString());
        $normalizer->addDenormalizer(FluentDenormalizer::for(ServiceCheckId::class)->as(static function (DenormalizationContextInterface $context) {
            return ServiceCheckId::fromString($context->getValue());
        }));

        $normalizer->addNormalizer(FluentNormalizer::for(HealthStatus::class)->asString());
        $normalizer->addDenormalizer(FluentDenormalizer::for(HealthStatus::class)->as(static function (DenormalizationContextInterface $context) {
            return new HealthStatus($context->getValue());
        }));
    }

    public function add(ServiceCheckThresholdCounter $counter): void
    {
        $this->store->addObject($this->getInternalId($counter->getServiceId(), $counter->getServiceCheckId()), $counter);
    }

    public function update(ServiceCheckThresholdCounter $counter): void
    {
        $this->store->updateObject($this->getInternalId($counter->getServiceId(), $counter->getServiceCheckId()), $counter);
    }

    public function remove(ServiceId $serviceId, ServiceCheckId $serviceCheckId): void
    {
        $this->store->removeObject($this->getInternalId($serviceId, $serviceCheckId));
    }

    public function find(ServiceId $serviceId, ServiceCheckId $serviceCheckId): ServiceCheckThresholdCounter
    {
        return $this->store->findById($this->getInternalId($serviceId, $serviceCheckId));
    }

    public function getInternalId(ServiceId $serviceId, ServiceCheckId $serviceCheckId): string
    {
        return $serviceCheckId.'@'.$serviceId;
    }
}
