<?php

namespace Tests\Morebec\Orkestra\Normalization\Denormalizer;

use Morebec\Orkestra\Normalization\Denormalizer\CallbackDenormalizer;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContext;
use PHPUnit\Framework\TestCase;

class CallbackDenormalizerTest extends TestCase
{
    public function testDenormalize(): void
    {
        $denormalizer = new CallbackDenormalizer(
            static function (DenormalizationContext $context) {
                return true;
            },
            static function (DenormalizationContext $context) {
                return 'a';
            },
        );

        $data = $denormalizer->denormalize(new DenormalizationContext('helloToA', 'string'));
        $this->assertEquals('a', $data);
    }

    public function testSupports(): void
    {
        $denormalizer = new CallbackDenormalizer(
            static function (DenormalizationContext $context) {
                return true;
            },
            static function (DenormalizationContext $context) {
                return 'a';
            },
        );
        $this->assertTrue($denormalizer->supports(new DenormalizationContext('helloToA', 'string')));
    }
}
