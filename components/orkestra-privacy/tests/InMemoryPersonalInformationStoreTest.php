<?php

namespace Tests\Morebec\Orkestra\Privacy;

use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\Privacy\InMemoryPersonalInformationStore;
use Morebec\Orkestra\Privacy\PersonalData;
use Morebec\Orkestra\Privacy\PersonalDataNotFoundException;
use PHPUnit\Framework\TestCase;

class InMemoryPersonalInformationStoreTest extends TestCase
{
    /**
     * @var InMemoryPersonalInformationStore
     */
    private $store;

    protected function setUp(): void
    {
        $this->store = new InMemoryPersonalInformationStore(new SystemClock());
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

        // Test replace
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $newReferenceToken = $this->store->put($record);

        $this->assertNotEquals($referenceToken, $newReferenceToken);
    }

    public function testReplace(): void
    {
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $referenceToken = $this->store->put($record);

        $this->store->replace($referenceToken, new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'user_account_settings'));

        $this->assertNotNull($referenceToken);

        $recorded = $this->store->findOneByReferenceToken($referenceToken);
        $this->assertEquals('user_account_settings', $recorded->getSource());

        $this->expectException(PersonalDataNotFoundException::class);
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $this->store->replace('not_found_token', $record);
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
}
