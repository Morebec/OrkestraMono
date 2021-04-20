<?php

namespace Morebec\Orkestra\PostgreSqlPersonalInformationStore;

class PostgreSqlPersonalInformationStoreConfiguration
{
    public $personallyIdentifiableInformationTableName = 'pii';

    /**
     * Encryption key used to encrypt the data
     * random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);.
     *
     * @var string
     */
    public $encryptionKey = null;
}
