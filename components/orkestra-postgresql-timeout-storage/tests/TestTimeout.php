<?php

namespace Tests\Morebec\Orkestra\PostgreSqlTimeoutStorage;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\Messaging\Timeout\AbstractTimeout;

class TestTimeout extends AbstractTimeout
{
    /** @var string */
    public $testData;

    public function __construct(string $id, DateTime $endsAt, $testData)
    {
        $this->testData = $testData;
        parent::__construct($id, $endsAt);
    }

    public static function getTypeName(): string
    {
        return 'test_timeout';
    }
}
