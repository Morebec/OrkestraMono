<?php

namespace Morebec\Orkestra\PostgreSqlPersonalInformationStore;

use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\Privacy\DisposedPersonalDataRemoverInterface;

/**
 * Service removing Personal data that is considered disposable.
 * This service should be run in a worker.
 */
class DisposedPersonalDataRemover implements DisposedPersonalDataRemoverInterface
{
    private PostgreSqlPersonalInformationStore $store;

    private ClockInterface $clock;

    public function __construct(PostgreSqlPersonalInformationStore $store, ?ClockInterface $clock = null)
    {
        $this->store = $store;
        $this->clock = $clock ?: new SystemClock();
    }

    public function run(): void
    {
        $conn = $this->store->getConnection();
        $conf = $this->store->getConfiguration();

        $qb = $conn->createQueryBuilder();
        $qb->delete($conf->personallyIdentifiableInformationTableName)
            ->where(sprintf('%s <= %s', PostgreSqlPersonalInformationStore::DISPOSED_AT_KEY, $qb->createPositionalParameter($this->clock->now())));

        $qb->executeStatement();
    }
}
