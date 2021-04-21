<?php

namespace Tests\Morebec\Orkestra\PostgreSqlTimerStorage;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\Messaging\Timer\AbstractTimer;

class TestTimer extends AbstractTimer
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
        return 'test_timer';
    }
}
