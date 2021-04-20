<?php

namespace Tests\Morebec\Orkestra\PostgreSqlPersonalInformationStore;

use Doctrine\DBAL\DriverManager;
use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\Normalization\ObjectNormalizer;
use Morebec\Orkestra\PostgreSqlPersonalInformationStore\PostgreSqlPersonalInformationStore;
use Morebec\Orkestra\PostgreSqlPersonalInformationStore\PostgreSqlPersonalInformationStoreConfiguration;
use Morebec\Orkestra\Privacy\PersonalData;
use PHPUnit\Framework\TestCase;

class PostgreSqlPersonalInformationStoreTest extends TestCase
{
    /**
     * @var PostgreSqlPersonalInformationStore
     */
    private $store;

    protected function setUp(): void
    {
        $clock = new SystemClock();
        $connection = DriverManager::getConnection([
            'url' => 'pgsql://postgres@localhost:5432/postgres?charset=UTF8',
        ]);
        $configuration = new PostgreSqlPersonalInformationStoreConfiguration();
        $configuration->encryptionKey = random_bytes(\SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
        $normalizer = new ObjectNormalizer();
        $this->store = new PostgreSqlPersonalInformationStore($connection, $configuration, $normalizer, $clock);
    }

    protected function tearDown(): void
    {
        $this->store->clear();
    }

    public function testErase(): void
    {
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $this->store->put($record);

        $this->store->erase('test-user-token');

        $this->assertEmpty($this->store->findByPersonalToken('test-user-token'));
    }

    public function testPut(): void
    {
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $referenceToken = $this->store->put($record);
        $this->assertNotNull($referenceToken);

        $record = $this->store->findOneByKeyName('test-user-token', 'emailAddress');
        $this->assertEquals('test@email.com', $record->getValue());

        // Test with nested array
        $record = new PersonalData('test-user-token', 'preferences', [
            'languages' => [
                'primary' => 'English',
                'secondary' => 'French',
            ],
        ], 'registration_form');
        $referenceToken = $this->store->put($record);

        $record = $this->store->findOneByReferenceToken($referenceToken);

        $this->assertEquals([
            'languages' => [
                'primary' => 'English',
                'secondary' => 'French',
            ],
        ], $record->getValue());
    }

    public function testFindByPersonalToken(): void
    {
        $records = [
            new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form'),
            new PersonalData('test-user-token', 'fullname', 'John Doe', 'registration_form'),
        ];

        foreach ($records as $record) {
            $this->store->put($record);
        }

        $found = $this->store->findByPersonalToken('test-user-token');

        $this->assertEquals(\count($records), \count($found));
    }

    public function testRemoveByKeyName(): void
    {
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $this->store->put($record);

        $this->store->removeByKeyName('test-user-token', $record->getKeyName());

        $found = $this->store->findOneByKeyName('test-user-token', 'emailAddress');

        $this->assertNull($found);
    }

    public function testFindOneByKeyName(): void
    {
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $reference = $this->store->put($record);

        $found = $this->store->findOneByKeyName('test-user-token', 'emailAddress');

        $this->assertEquals($reference, $found->getReferenceToken());

        $this->assertNull($this->store->findOneByKeyName('test-user-token', 'not-found'));
    }

    public function testFindOneByReferenceToken(): void
    {
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $reference = $this->store->put($record);

        $found = $this->store->findOneByReferenceToken($reference);

        $this->assertNotNull($found);

        $this->assertEquals('test@email.com', $found->getValue());
    }

    public function testRemove(): void
    {
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $reference = $this->store->put($record);

        $this->store->remove($reference);

        $found = $this->store->findOneByReferenceToken($reference);
        $this->assertNull($found);
    }

    public function testRotateKey(): void
    {
        $referenceFullname = $this->store->put(new PersonalData('test-user-token', 'fullname', 'Jane Doe', 'registration_form'));
        $referenceEmailAddress = $this->store->put(new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form'));
        $referencePreferences = $this->store->put(new PersonalData('test-user-token', 'preferences', [
            'colorMode' => 'DARK',
            'language' => 'ENGLISH',
        ], 'registration_form'));

        $this->store->rotateKey(random_bytes(\SODIUM_CRYPTO_SECRETBOX_KEYBYTES));

        $fullnameRecord = $this->store->findOneByReferenceToken($referenceFullname);
        $this->assertNotNull($fullnameRecord);
        $this->assertEquals('Jane Doe', $fullnameRecord->getValue());

        $emailRecord = $this->store->findOneByReferenceToken($referenceEmailAddress);
        $this->assertNotNull($emailRecord);
        $this->assertEquals('test@email.com', $emailRecord->getValue());

        $preferencesRecord = $this->store->findOneByReferenceToken($referencePreferences);
        $this->assertEquals([
            'colorMode' => 'DARK',
            'language' => 'ENGLISH',
        ], $preferencesRecord->getValue());
    }
}
