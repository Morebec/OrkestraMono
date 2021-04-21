<?php

namespace Tests\Morebec\Orkestra\Normalization\Normalizer;

use Morebec\Orkestra\Normalization\Normalizer\CallbackNormalizer;
use Morebec\Orkestra\Normalization\Normalizer\NormalizationContext;
use PHPUnit\Framework\TestCase;

class CallbackNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new CallbackNormalizer(
            static function (NormalizationContext $context) {
                return true;
            },
            static function (NormalizationContext $context) {
                return 'a';
            },
        );

        $data = $normalizer->normalize(new NormalizationContext('helloToA'));
        $this->assertEquals('a', $data);
    }

    public function testSupports(): void
    {
        $normalizer = new CallbackNormalizer(
            static function (NormalizationContext $context) {
                return true;
            },
            static function (NormalizationContext $context) {
                return 'a';
            },
        );
        $this->assertTrue($normalizer->supports(new NormalizationContext('helloToA')));
    }
}
