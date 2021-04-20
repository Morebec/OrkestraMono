<?php

namespace Tests\Morebec\Orkestra\Normalization\Denormalizer;

use Morebec\Orkestra\DateTime\Date;
use Morebec\Orkestra\Normalization\Denormalizer\DateTimeDenormalizer;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContext;
use PHPUnit\Framework\TestCase;

class DateTimeDenormalizerTest extends TestCase
{
    public function testSupports(): void
    {
        $denormalizer = new DateTimeDenormalizer();
        $this->assertTrue($denormalizer->supports(new DenormalizationContext('1970-01-01T00:00:00.000+00:00', Date::class)));
    }

    public function testDenormalize(): void
    {
        $denormalizer = new DateTimeDenormalizer();
        $value = $denormalizer->denormalize(new DenormalizationContext('1970-01-01T00:00:00.000+00:00', Date::class));
        $this->assertEquals(new Date('01-01-1970'), $value);

        // Test other format YYYY-MM-DD
        $value = $denormalizer->denormalize(new DenormalizationContext('1970-01-01', Date::class));
        $this->assertEquals(new Date('01-01-1970'), $value);

        // Test other format YYYY/MM/DD
        $value = $denormalizer->denormalize(new DenormalizationContext('1970/01/01', Date::class));
        $this->assertEquals(new Date('01-01-1970'), $value);
    }
}
