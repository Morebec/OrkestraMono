<?php

namespace Tests\Morebec\Orkestra\Normalization\Denormalizer;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContext;
use Morebec\Orkestra\Normalization\Denormalizer\Denormalizer;
use PHPUnit\Framework\TestCase;

class DenormalizerTest extends TestCase
{
    public function testDenormalize()
    {
        $obj = $this->createNullableDateTimeObject();
        // Make sure strings for date times that are nullable work as expected
        $denormalizer = new Denormalizer();

        $data = [
            'date' => '2020-01-01T00:00:00.000+00:00',
        ];

        $denormalizedObject = $denormalizer->denormalize(new DenormalizationContext($data, \get_class($obj)));

        $this->assertEquals(
            DateTime::createFromFormat(DateTime::RFC3339_EXTENDED, '2020-01-01T00:00:00.000+00:00'),
            $denormalizedObject->date
        );
    }

    public function createNullableDateTimeObject()
    {
        return new class() {
            /** @var DateTime|null */
            public $date;

            public function __construct()
            {
            }
        };
    }
}
