<?php

namespace Tests\Morebec\Orkestra\Privacy;

use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\Privacy\InMemoryPersonalInformationStore;
use Morebec\Orkestra\Privacy\PersonalData;
use Morebec\Orkestra\Privacy\PersonalDataFoundException;
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

        self::assertEmpty($this->store->findByPersonalToken('test-user-token'));
    }

    public function testPut(): void
    {
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $referenceToken = $this->store->put($record);

        self::assertNotNull($referenceToken);

        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $this->expectException(PersonalDataFoundException::class);
        $this->store->put($record);
    }

    public function testReplace(): void
    {
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $referenceToken = $this->store->put($record);

        $this->store->replace($referenceToken, new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'user_account_settings'));

        self::assertNotNull($referenceToken);

        $recorded = $this->store->findOneByReferenceToken($referenceToken);
        self::assertEquals('user_account_settings', $recorded->getSource());

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

        self::assertEquals(\count($records), \count($found));
    }

    public function testRemoveByKeyName(): void
    {
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $this->store->put($record);

        $this->store->removeByKeyName('test-user-token', $record->getKeyName());

        $found = $this->store->findOneByKeyName('test-user-token', 'emailAddress');

        self::assertNull($found);
    }

    public function testFindOneByKeyName(): void
    {
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $reference = $this->store->put($record);

        $found = $this->store->findOneByKeyName('test-user-token', 'emailAddress');

        self::assertEquals($reference, $found->getReferenceToken());

        self::assertNull($this->store->findOneByKeyName('test-user-token', 'not-found'));
    }

    public function testFindOneByReferenceToken(): void
    {
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $reference = $this->store->put($record);

        $found = $this->store->findOneByReferenceToken($reference);

        self::assertNotNull($found);

        self::assertEquals('test@email.com', $found->getValue());

        // CUSTOM REFERENCE TOKEN
        $record = new PersonalData('test-user-token', 'secondaryEmailAddress', 'test@email.com', 'registration_form');
        $customToken = 'zGh6YxO0p65Rtgkl';
        $record->referenceToken($customToken);
        $reference = $this->store->put($record);

        self::assertEquals($customToken, $reference);

        $found = $this->store->findOneByReferenceToken($reference);

        self::assertNotNull($found);

        self::assertEquals('test@email.com', $found->getValue());
    }

    public function testRemove(): void
    {
        $record = new PersonalData('test-user-token', 'emailAddress', 'test@email.com', 'registration_form');
        $reference = $this->store->put($record);

        $this->store->remove($reference);

        $found = $this->store->findOneByReferenceToken($reference);
        self::assertNull($found);
    }
}
