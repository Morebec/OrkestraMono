<?php

namespace Tests\Morebec\Orkestra\PostgreSqlDocumentStore;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\PostgreSqlDocumentStore\Filter\Filter;
use Morebec\Orkestra\PostgreSqlDocumentStore\Filter\FilterOperator;
use Morebec\Orkestra\PostgreSqlDocumentStore\PostgreSqlDocumentStore;
use Morebec\Orkestra\PostgreSqlDocumentStore\PostgreSqlDocumentStoreConfiguration;
use PHPUnit\Framework\TestCase;

class PostgreSqlDocumentStoreTest extends TestCase
{
    /**
     * @var PostgreSqlDocumentStore
     */
    private $store;

    protected function setUp(): void
    {
        $clock = new SystemClock();
        $config = new PostgreSqlDocumentStoreConfiguration();
        $connection = DriverManager::getConnection([
            'url' => 'pgsql://postgres@localhost:5432/postgres?charset=UTF8',
        ], new Configuration());

        $this->store = new PostgreSqlDocumentStore($connection, $config, $clock);
        $this->store->clear();
    }

    protected function tearDown(): void
    {
        $this->store->clear();
    }

    public function testInsertDocument(): void
    {
        $id = uniqid('doc_');
        $this->store->insertDocument('test_insert_document', $id, [
            'hello' => 'world',
        ]);

        $data = $this->store->findOneDocument('test_insert_document', Filter::findById($id));

        $this->assertEquals(['hello' => 'world'], $data);
    }

    public function testFindOneDocument(): void
    {
        $id = uniqid('doc_');
        $this->store->insertDocument('test_insert_document', $id, [
            'hello' => 'world',
        ]);

        // Filter
        $data = $this->store->findOneDocument('test_insert_document', Filter::findById($id));
        $this->assertEquals(['hello' => 'world'], $data);
        // String
        $data = $this->store->findOneDocument('test_insert_document', "id = '{$id}'");
        $this->assertEquals(['hello' => 'world'], $data);

        // Filter
        $data = $this->store->findOneDocument('test_insert_document', Filter::findByField('hello', FilterOperator::EQUAL(), 'world'));
        $this->assertEquals(['hello' => 'world'], $data);
        // String
        $data = $this->store->findOneDocument('test_insert_document', "data->>'hello' = 'world'");
        $this->assertEquals(['hello' => 'world'], $data);

        // Filter
        $data = $this->store->findOneDocument('test_insert_document', Filter::findByField('hello', FilterOperator::EQUAL(), 'planet'));
        $this->assertNull($data);

        // String
        $data = $this->store->findOneDocument('test_insert_document', "data->>'hello' = 'planet'");
        $this->assertNull($data);

        $clock = new SystemClock();
        $id = uniqid('doc_');
        $doc = [
            'username' => 'postgresql',
            'nbTokens' => 5,
            'emailAddresses' => [
                'primary' => 'primary@postgres.com',
                'secondary' => 'secondary@postgres.com',
            ],
            'registeredSince' => json_encode($clock->now()->subDays(5)->toAtomString()),
        ];
        $this->store->insertDocument('test_insert_document', $id, $doc);

        // String
        $data = $this->store->findOneDocument('test_insert_document', "data->'emailAddresses'->>'primary' = 'primary@postgres.com' OR data->'emailAddresses'->>'secondary' = 'secondary@postgres.com'");
        $this->assertEquals($doc, $data);

        // Filter
        $data = $this->store->findOneDocument('test_insert_document',
            Filter::where('emailAddresses.primary', FilterOperator::EQUAL(), 'primary@postgres.com')
                ->or('emailAddresses.secondary', FilterOperator::EQUAL(), 'secondary@postgres.com')
        )
        ;
        $this->assertEquals($doc, $data);

        // String
        $date = $clock->now()->toAtomString();
        $data = $this->store->findOneDocument('test_insert_document', "(data->>'registeredSince')::TIMESTAMP <= '$date'::TIMESTAMP");
        $this->assertEquals($doc, $data);

        // Filter
        $data = $this->store->findOneDocument('test_insert_document', Filter::where('registeredSince', FilterOperator::LESS_OR_EQUAL(), $clock->now()));
        $this->assertEquals($doc, $data);

        // Test with Cast
        $data = $this->store->findOneDocument('test_insert_document', Filter::where('nbTokens', FilterOperator::GREATER_OR_EQUAL(), 5, 'INTEGER'));
        $this->assertEquals($doc, $data);
    }

    public function testHasCollection(): void
    {
        $this->store->insertDocument('testDropCollection', uniqid(), []);
        $this->assertTrue($this->store->hasCollection('testDropCollection'));
    }

    public function testDropCollection(): void
    {
        $this->store->insertDocument('testDropCollection', uniqid(), []);
        $this->store->dropCollection('testDropCollection');
        $this->assertFalse($this->store->hasCollection('testDropCollection'));
    }

    public function testCreateCollection(): void
    {
        $this->store->createCollection('testCreateCollection');
        $this->assertTrue($this->store->hasCollection('testCreateCollection'));
    }

    public function testFindManyDocuments(): void
    {
        $docs = [
            [
                'username' => 'postgresql',
                'emailAddresses' => [
                    'primary' => 'primary@postgres.com',
                    'secondary' => 'secondary@postgres.com',
                ],
            ],

            [
                'username' => 'mongo',
                'emailAddresses' => [
                    'primary' => 'primary@mongo.com',
                    'secondary' => 'secondary@mongo.com',
                ],
            ],
        ];

        $this->store->insertDocument('testFindManyDocuments', $docs[0]['username'], $docs[0]);
        $this->store->insertDocument('testFindManyDocuments', $docs[1]['username'], $docs[1]);

        $found = $this->store->findManyDocuments('testFindManyDocuments', "data->>'username' IS NOT NULL");
        $this->assertEquals($docs, $found);

        $found = $this->store->findManyDocuments('testFindManyDocuments', "data->>'username' = 'postgresql'");
        $this->assertEquals([$docs[0]], $found);
    }

    public function testListCollections(): void
    {
        $this->store->createCollection('testListCollections');
        $collections = $this->store->listCollections();

        $this->assertEquals(['testlistcollections'], $collections);
    }

    public function testUpdateDocument(): void
    {
        $docs = [
            [
                'username' => 'postgresql',
                'emailAddresses' => [
                    'primary' => 'primary@postgres.com',
                    'secondary' => 'secondary@postgres.com',
                ],
            ],

            [
                'username' => 'mongo',
                'emailAddresses' => [
                    'primary' => 'primary@mongo.com',
                    'secondary' => 'secondary@mongo.com',
                ],
            ],
        ];

        $this->store->insertDocument('testUpdateDocument', $docs[0]['username'], $docs[0]);
        $this->store->insertDocument('testUpdateDocument', $docs[1]['username'], $docs[1]);

        $updated = [
            'username' => 'postgreNoSql',
            'emailAddresses' => [
                'primary' => 'primary@postgres.com',
                'secondary' => 'secondary@postgres.com',
            ],
        ];
        $this->store->updateDocument('testUpdateDocument', $docs[0]['username'], $updated);

        $found = $this->store->findOneDocument('testUpdateDocument', "data->>'username' = 'postgreNoSql'");
        $this->assertEquals($updated, $found);
    }

    public function testRenameCollection(): void
    {
        $this->store->createCollection('testRenameCollection');
        $this->assertTrue($this->store->hasCollection('testRenameCollection'));
        $this->store->renameCollection('testRenameCollection', 'testRenameCollectionRENAMED');
        $this->assertTrue($this->store->hasCollection('testRenameCollectionRENAMED'));
    }
}
