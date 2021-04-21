<?php

namespace Tests\Morebec\Orkestra\Messaging\Normalization;

use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Normalization\ClassMapMessageNormalizer;
use Morebec\Orkestra\Messaging\Normalization\MessageClassMap;
use PHPUnit\Framework\TestCase;

class ClassMapMessageNormalizerTest extends TestCase
{
    public function testNormalize()
    {
        $message = $this->createMessage();
        $classMap = new MessageClassMap([
            'test_message' => \get_class($message),
        ]);

        $normalizer = new ClassMapMessageNormalizer($classMap);

        $data = $normalizer->normalize($message);

        $this->assertEquals([
            'messageTypeName' => 'test_message',
            'field' => 'value',
        ], $data);
    }

    public function testDenormalize()
    {
        $message = $this->createMessage();
        $classMap = new MessageClassMap([
            'test_message' => \get_class($message),
        ]);

        $normalizer = new ClassMapMessageNormalizer($classMap);

        $data = $normalizer->normalize($message);
        $rehydrated = $normalizer->denormalize($data);

        $this->assertEquals($message, $rehydrated);
    }

    private function createMessage(): MessageInterface
    {
        return new class() implements MessageInterface {
            /** @var string */
            public $field = 'value';

            public static function getTypeName(): string
            {
                return 'test_message';
            }
        };
    }
}
