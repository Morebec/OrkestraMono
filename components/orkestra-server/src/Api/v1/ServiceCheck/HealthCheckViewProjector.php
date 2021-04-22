<?php

namespace Morebec\Orkestra\OrkestraServer\Api\v1\ServiceCheck;

use Morebec\Orkestra\EventSourcing\Projection\AbstractTypedEventProjector;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\Normalization\ObjectNormalizerInterface;
use Morebec\Orkestra\OrkestraServer\Core\Persistence\PostgreSqlObjectStore;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\RunHealthCheck\HealthCheckEndedEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\RunHealthCheck\HealthCheckStartedEvent;
use Morebec\Orkestra\PostgreSqlDocumentStore\PostgreSqlDocumentStore;

class HealthCheckViewProjector extends AbstractTypedEventProjector
{
    /**
     * @var PostgreSqlObjectStore
     */
    private $objectStore;

    public function __construct(
        PostgreSqlDocumentStore $documentStore,
        ObjectNormalizerInterface $objectNormalizer,
        MessageNormalizerInterface $messageNormalizer
    ) {
        parent::__construct($messageNormalizer);

        $this->objectStore = new PostgreSqlObjectStore(
            $documentStore,
            self::getTypeName(),
            HealthCheckView::class,
            $objectNormalizer
        );
    }

    public function boot(): void
    {
    }

    public function shutdown(): void
    {
    }

    public function reset(): void
    {
        $this->objectStore->clear();
    }

    public static function getTypeName(): string
    {
        return 'api_v1_service_health_check';
    }

    public function onHealthCheckStarted(HealthCheckStartedEvent $event): void
    {
        $hc = new HealthCheckView();
        $hc->id = $event->healthCheckId;
        $hc->serviceId = $event->serviceId;
        $hc->serviceCheckId = $event->serviceCheckId;
        $hc->startedAt = $event->startedAt;
        $hc->url = $event->url;

        $this->objectStore->addObject($hc->id, $hc);
    }

    public function onHealthCheckEnded(HealthCheckEndedEvent $event): void
    {
        /** @var HealthCheckView $hc */
        $hc = $this->objectStore->findById($event->healthCheckId);

        $hc->endedAt = $event->endedAt;
        $hc->status = $event->status;
        $hc->timeout = $event->responseTimedOut;
        $hc->response = new HealthCheckResponseView(
            $event->responseStatusCode,
            $event->responseHeaders,
            $event->responsePayload,
        );

        $this->objectStore->updateObject($hc->id, $hc);
    }
}
