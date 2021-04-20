<?php

namespace Tests\Morebec\Orkestra\Normalization\Normalizer;

use Morebec\Orkestra\DateTime\Date;
use Morebec\Orkestra\Normalization\Normalizer\DateTimeNormalizer;
use Morebec\Orkestra\Normalization\Normalizer\NormalizationContext;
use PHPUnit\Framework\TestCase;

class DateTimeNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new DateTimeNormalizer();
        $data = $normalizer->normalize(new NormalizationContext(new Date('01-01-1970')));

        $this->assertIsString($data);
        $this->assertEquals('1970-01-01T00:00:00.000+00:00', $data);
    }

    public function testSupports(): void
    {
        $normalizer = new DateTimeNormalizer();
        $this->assertTrue($normalizer->supports(new NormalizationContext(new Date())));
    }
}
