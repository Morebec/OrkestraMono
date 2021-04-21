<?php

namespace Tests\Morebec\Orkestra\Normalization;

use Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer\CannotDenormalizePropertyException;
use Morebec\Orkestra\Normalization\ObjectNormalizer;
use PHPUnit\Framework\TestCase;

class ObjectNormalizerTest extends TestCase
{
    public function testDenormalize(): void
    {
        $normalizer = new ObjectNormalizer();

        $headers = new TestObject([
            'hello' => 'world',
            'foo' => 'bar',
        ]);

        $data = $normalizer->normalize($headers);

        $object = $normalizer->denormalize($data, TestObject::class);

        $this->assertEquals($headers, $object);

        $this->expectException(CannotDenormalizePropertyException::class);
        $normalizer->denormalize([
            'values' => [
                'hello' => 'world',
                'foo' => 'bar',
            ],
            'compound' => 1564, // This should be a string or a bool
        ], TestObject::class);
    }

    public function testNormalize(): void
    {
        $normalizer = new ObjectNormalizer();

        $headers = new TestObject([
            'hello' => 'world',
            'foo' => 'bar',
        ]);

        $data = $normalizer->normalize($headers);

        $this->assertEquals([
            'values' => [
                'hello' => 'world',
                'foo' => 'bar',
            ],
            'compound' => true,
        ], $data);
    }
}

/**
 * @internal
 */
class TestObject
{
    /** @var array */
    private $values;

    /** @var string|bool */
    private $compound = true;

    public function __construct(array $values)
    {
        $this->values = $values;
    }
}
