<?php

namespace Tests\Morebec\Orkestra\Normalization\Denormalizer;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContext;
use Morebec\Orkestra\Normalization\Denormalizer\Denormalizer;
use PHPUnit\Framework\TestCase;

class DenormalizerTest extends TestCase
{
    public function testDenormalizePropertySubset(): void
    {
        $obj = $this->createObject();

        $denormalizer = new Denormalizer();

        $data = [
            'username' => 'user123',
            'emailAddress' => 'user123@email.com',

            // Having additional properties should not throw error, and should only be ignored.
            'additionalProperty' => 456,
        ];

        $denormalizedObject = $denormalizer->denormalize(new DenormalizationContext($data, \get_class($obj)));

        $this->assertEquals($data['username'], $denormalizedObject->username);
        $this->assertEquals($data['emailAddress'], $denormalizedObject->emailAddress);
    }

    public function testDenormalizeNullableDateTime()
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

        $data = [
            'date' => null,
        ];

        $denormalizedObject = $denormalizer->denormalize(new DenormalizationContext($data, \get_class($obj)));

        $this->assertNull($denormalizedObject->date);
    }

    private function createObject()
    {
        return new class() {
            /** @var string */
            public $username;

            /** @var string|null */
            public $emailAddress;

            public function __construct()
            {
            }
        };
    }

    private function createNullableDateTimeObject()
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
