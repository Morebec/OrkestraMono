<?php

namespace Tests\Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging;

use Morebec\Orkestra\Messaging\Normalization\ClassMapMessageNormalizer;
use Morebec\Orkestra\Normalization\Denormalizer\ScalarValueDenormalizer;
use Morebec\Orkestra\Normalization\Normalizer\ScalarValueNormalizer;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging\MessageNormalizerConfiguration;
use PHPUnit\Framework\TestCase;

class MessageNormalizerConfigurationTest extends TestCase
{
    public function testWithNormalizationPair(): void
    {
        $configuration = new MessageNormalizerConfiguration();
        $configuration->withNormalizationPair(
            ScalarValueNormalizer::class,
            ScalarValueDenormalizer::class
        );

        self::assertCount(1, $configuration->normalizers);
        self::assertCount(1, $configuration->denormalizers);
    }

    public function testWithNormalizer(): void
    {
        $configuration = new MessageNormalizerConfiguration();
        $configuration->withNormalizer(ScalarValueNormalizer::class);
        self::assertCount(1, $configuration->normalizers);
    }

    public function testWithDenormalizer(): void
    {
        $configuration = new MessageNormalizerConfiguration();
        $configuration->withDenormalizer(ScalarValueDenormalizer::class);
        self::assertCount(1, $configuration->denormalizers);
    }

    public function testUsingDefaultImplementation(): void
    {
        $configuration = new MessageNormalizerConfiguration();
        $configuration->usingDefaultImplementation();

        self::assertEquals(ClassMapMessageNormalizer::class, $configuration->implementationClassName);
    }

    public function testUsingImplementation(): void
    {
        $configuration = new MessageNormalizerConfiguration();
        $configuration->usingImplementation(ClassMapMessageNormalizer::class);

        self::assertEquals(ClassMapMessageNormalizer::class, $configuration->implementationClassName);
    }
}
