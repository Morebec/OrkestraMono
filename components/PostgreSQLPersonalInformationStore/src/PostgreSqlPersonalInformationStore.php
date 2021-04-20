<?php

namespace Morebec\Orkestra\PostgreSqlPersonalInformationStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\Normalization\ObjectNormalizer;
use Morebec\Orkestra\Normalization\ObjectNormalizerInterface;
use Morebec\Orkestra\Privacy\PersonalDataInterface;
use Morebec\Orkestra\Privacy\PersonalInformationStoreInterface;
use Morebec\Orkestra\Privacy\RecordedPersonalData;
use Morebec\Orkestra\Privacy\RecordedPersonalDataInterface;
use Ramsey\Uuid\Uuid;

class PostgreSqlPersonalInformationStore implements PersonalInformationStoreInterface
{
    public const PERSONAL_TOKEN_KEY = 'personal_token';

    public const REFERENCE_TOKEN_KEY = 'reference_token';

    public const DATA_KEY = 'data';

    public const DISPOSED_AT_KEY = 'disposed_at';

    public const KEY_NAME_KEY = 'key_name';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PostgreSqlPersonalInformationStoreConfiguration
     */
    private $configuration;
    /**
     * @var ObjectNormalizerInterface
     */
    private $normalizer;
    /**
     * @var ClockInterface
     */
    private $clock;

    public function __construct(Connection $connection, PostgreSqlPersonalInformationStoreConfiguration $config, ?ObjectNormalizerInterface $normalizer = null, ?ClockInterface $clock = null)
    {
        if (!$normalizer) {
            $normalizer = new ObjectNormalizer();
        }

        if (!$clock) {
            $clock = new SystemClock();
        }

        $this->configuration = $config;
        $this->connection = $connection;

        if (!$this->configuration->encryptionKey) {
            throw new \InvalidArgumentException(sprintf('No encryptionKey provided: Please provide a random value of %s bytes.', \SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
        }

        $this->openConnection();
        $this->setupSchema($config);
        $this->normalizer = $normalizer;
        $this->clock = $clock;
    }

    public function __destruct()
    {
        $this->closeConnection();
    }

    public static function generateEncryptionKey(): string
    {
        return random_bytes(\SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    }

    public function put(PersonalDataInterface $data): string
    {
        $personalToken = $data->getPersonalToken();
        $referenceToken = sprintf('pii:%s/%s', $personalToken, Uuid::uuid4());
        $disposedAt = $data->getDisposedAt();

        $recorded = new RecordedPersonalData(
            $data->getPersonalToken(),
            $referenceToken,
            $data->getKeyName(),
            $data->getValue(),
            $data->getSource(),
            $data->getReasons(),
            $data->getProcessingRequirements(),
            $data->getDisposedAt(),
            $data->getMetadata(),
            $this->clock->now()
        );
        $normalizedData = $this->normalizer->normalize($recorded);
        $encryptedData = $this->encryptRecord($normalizedData, $this->configuration->encryptionKey);

        $keys = [
            'personalToken' => self::PERSONAL_TOKEN_KEY,
            'referenceToken' => self::REFERENCE_TOKEN_KEY,
            'data' => self::DATA_KEY,
            'disposedAt' => self::DISPOSED_AT_KEY,
            'keyName' => self::KEY_NAME_KEY,
        ];

        $sql = <<<SQL
            INSERT INTO {$this->configuration->personallyIdentifiableInformationTableName}
                ({$keys['personalToken']}, {$keys['referenceToken']}, {$keys['keyName']}, {$keys['disposedAt']}, {$keys['data']})
                VALUES (:personalToken, :referenceToken, :keyName, :disposedAt, :personalData)
                ON CONFLICT ({$keys['personalToken']}, {$keys['keyName']})
                DO UPDATE
                SET {$keys['personalToken']} = excluded.{$keys['personalToken']},
                    {$keys['referenceToken']} = excluded.{$keys['referenceToken']},
                    {$keys['keyName']} = excluded.{$keys['keyName']},
                    {$keys['disposedAt']} = excluded.{$keys['disposedAt']},
                    {$keys['data']} = excluded.{$keys['data']}
            ;
            SQL;

        $this->connection->executeStatement($sql, [
            'personalToken' => $personalToken,
            'referenceToken' => $referenceToken,
            'keyName' => $data->getKeyName(),
            'disposedAt' => $disposedAt,
            'personalData' => $encryptedData,
        ]);

        return $referenceToken;
    }

    public function findOneByKeyName(string $personalToken, string $keyName): ?RecordedPersonalDataInterface
    {
        $qb = $this->connection->createQueryBuilder();

        $qb
            ->select(self::DATA_KEY)
            ->from($this->configuration->personallyIdentifiableInformationTableName)
            ->where(sprintf('%s = %s', self::PERSONAL_TOKEN_KEY, $qb->createPositionalParameter($personalToken)))
            ->andWhere(sprintf('%s = %s', self::KEY_NAME_KEY, $qb->createPositionalParameter($keyName)))
        ;

        $result = $qb->execute();

        $record = $result->fetchAssociative();

        if ($record === false) {
            return null;
        } else {
            return $this->normalizer->denormalize($this->decryptRecord($record[self::DATA_KEY], $this->configuration->encryptionKey), RecordedPersonalData::class);
        }
    }

    public function findOneByReferenceToken(string $referenceToken): ?RecordedPersonalDataInterface
    {
        $qb = $this->connection->createQueryBuilder();

        $qb
            ->select(self::DATA_KEY)
            ->from($this->configuration->personallyIdentifiableInformationTableName)
            ->where(sprintf('%s = %s', self::REFERENCE_TOKEN_KEY, $qb->createPositionalParameter($referenceToken)))
        ;

        $result = $qb->execute();

        $record = $result->fetchAssociative();

        if ($record === false) {
            return null;
        } else {
            return $this->normalizer->denormalize($this->decryptRecord($record[self::DATA_KEY], $this->configuration->encryptionKey), RecordedPersonalData::class);
        }
    }

    public function findByPersonalToken(string $personalToken): array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb
            ->select(self::DATA_KEY)
            ->from($this->configuration->personallyIdentifiableInformationTableName)
            ->where(sprintf('%s = %s', self::PERSONAL_TOKEN_KEY, $qb->createPositionalParameter($personalToken)))
        ;

        $result = $qb->execute();

        $records = [];

        while ($record = $result->fetchAssociative()) {
            $records[] = $this->normalizer->denormalize($this->decryptRecord($record[self::DATA_KEY], $this->configuration->encryptionKey), RecordedPersonalData::class);
        }

        return $records;
    }

    public function removeByKeyName(string $personalToken, string $keyName): void
    {
        $this->connection->delete($this->configuration->personallyIdentifiableInformationTableName, [
            self::PERSONAL_TOKEN_KEY => $personalToken,
            self::KEY_NAME_KEY => $keyName,
        ]);
    }

    public function remove(string $referenceToken): void
    {
        $this->connection->delete($this->configuration->personallyIdentifiableInformationTableName, [
            self::REFERENCE_TOKEN_KEY => $referenceToken,
        ]);
    }

    public function erase(string $personalToken): void
    {
        $this->connection->delete($this->configuration->personallyIdentifiableInformationTableName, [
            self::PERSONAL_TOKEN_KEY => $personalToken,
        ]);
    }

    public function openConnection(): void
    {
        $this->connection->connect();
    }

    public function closeConnection(): void
    {
        $this->connection->close();
    }

    public function clear(): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->connection->executeQuery($platform->getTruncateTableSQL($this->configuration->personallyIdentifiableInformationTableName));
    }

    public function getConfiguration(): PostgreSqlPersonalInformationStoreConfiguration
    {
        return $this->configuration;
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * Rotates the data to use a new given encryption key.
     * It also changes the configuration object of this store to use this new key.
     */
    public function rotateKey(string $encryptionKey): void
    {
        $qb = $this->connection->createQueryBuilder();

        $qb
            ->select(self::DATA_KEY)
            ->from($this->configuration->personallyIdentifiableInformationTableName)
        ;

        $result = $qb->execute();

        $rotateKeyForRecords = function () use ($result, $encryptionKey) {
            while ($record = $result->fetchAssociative()) {
                /** @var RecordedPersonalData $record */
                $record = $this->normalizer->denormalize($this->decryptRecord($record[self::DATA_KEY], $this->configuration->encryptionKey), RecordedPersonalData::class);
                $this->rotateRecord($record->getReferenceToken(), $record, $encryptionKey);
            }
            $this->configuration->encryptionKey = $encryptionKey;
        };
        $rotateKeyForRecords->bindTo($this);

        $this->connection->transactional($rotateKeyForRecords);
    }

    private function encryptRecord(array $normalizedRecord, string $encryptionKey): string
    {
        $valueStr = json_encode($normalizedRecord);

        $nonce = random_bytes(\SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($valueStr, $nonce, $encryptionKey);

        $encoded = $nonce.$ciphertext;

        // Because of a bug in DBAL, we must encode to base64: https://github.com/doctrine/orm/issues/4029
        return base64_encode($encoded);
    }

    private function decryptRecord($encryptedRecord, string $encryptionKey): ?array
    {
        // DBAL returns a stream for BYTEA values
        $encryptedRecord = stream_get_contents($encryptedRecord);
        $decoded = base64_decode($encryptedRecord);

        $nonce = mb_substr($decoded, 0, \SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($decoded, \SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
        $rawValue = sodium_crypto_secretbox_open($ciphertext, $nonce, $encryptionKey);

        return $rawValue !== false ? json_decode($rawValue, true) : null;
    }

    private function rotateRecord(string $referenceToken, RecordedPersonalData $data, string $encryptionKey): void
    {
        $normalizedData = $this->normalizer->normalize($data);
        $encryptedData = $this->encryptRecord($normalizedData, $encryptionKey);

        $this->connection->update($this->configuration->personallyIdentifiableInformationTableName, [
            self::DATA_KEY => $encryptedData,
        ], [self::REFERENCE_TOKEN_KEY => $referenceToken]);
    }

    private function setupSchema(PostgreSqlPersonalInformationStoreConfiguration $configuration): void
    {
        $schema = new Schema();

        $sm = $this->connection->getSchemaManager();
        if (!$sm->tablesExist($configuration->personallyIdentifiableInformationTableName)) {
            $piiTable = $schema->createTable($configuration->personallyIdentifiableInformationTableName);

            $piiTable->addColumn(self::REFERENCE_TOKEN_KEY, 'string', ['notnull' => true]);
            $piiTable->setPrimaryKey([self::REFERENCE_TOKEN_KEY]);

            $piiTable->addColumn(self::PERSONAL_TOKEN_KEY, 'string', ['notnull' => true]);
            $piiTable->addIndex([self::PERSONAL_TOKEN_KEY]);

            $piiTable->addColumn(self::KEY_NAME_KEY, 'string', ['notnull' => true]);
            $piiTable->addUniqueIndex([self::PERSONAL_TOKEN_KEY, self::KEY_NAME_KEY]);

            $piiTable->addColumn(self::DATA_KEY, 'blob', ['notnull' => true]);

            $piiTable->addColumn(self::DISPOSED_AT_KEY, 'datetime', ['notnull' => false]);
            $piiTable->addIndex([self::DISPOSED_AT_KEY]);
        }

        $queries = $schema->toSql($this->connection->getDatabasePlatform());

        foreach ($queries as $query) {
            // Since DBAL does not allow to specify JSONB, but only JSON, we specify it here.
            $query = str_replace('JSON', 'JSONB', $query);
            $this->connection->executeQuery($query);
        }
    }
}
