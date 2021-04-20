<?php

namespace Tests\Morebec\Orkestra\PostgreSqlPersonalInformationStore;

use Doctrine\DBAL\DriverManager;
use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\Normalization\ObjectNormalizer;
use Morebec\Orkestra\PostgreSqlPersonalInformationStore\DisposedPersonalDataRemover;
use Morebec\Orkestra\PostgreSqlPersonalInformationStore\PostgreSqlPersonalInformationStore;
use Morebec\Orkestra\PostgreSqlPersonalInformationStore\PostgreSqlPersonalInformationStoreConfiguration;
use Morebec\Orkestra\Privacy\PersonalData;
use PHPUnit\Framework\TestCase;

class DisposedPersonalDataRemoverTest extends TestCase
{
    /**
     * @var PostgreSqlPersonalInformationStore
     */
    private $store;
    private $clock;

    protected function setUp(): void
    {
        $this->clock = new SystemClock();
        $connection = DriverManager::getConnection([
            'url' => 'pgsql://postgres@localhost:5432/postgres?charset=UTF8',
        ]);
        $configuration = new PostgreSqlPersonalInformationStoreConfiguration();
        $configuration->encryptionKey = random_bytes(\SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

        $normalizer = new ObjectNormalizer();
        $this->store = new PostgreSqlPersonalInformationStore($connection, $configuration, $normalizer, $this->clock);
    }

    protected function tearDown(): void
    {
        $this->store->clear();
    }

    public function testRun(): void
    {
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $record->disposedAt($this->clock->now()->subDays(5));
        $referenceToken = $this->store->put($record);

        $this->assertNotNull($this->store->findOneByReferenceToken($referenceToken));

        $remover = new DisposedPersonalDataRemover($this->store, $this->clock);

        $remover->run();

        $this->assertNull($this->store->findOneByReferenceToken($referenceToken));
    }
}
