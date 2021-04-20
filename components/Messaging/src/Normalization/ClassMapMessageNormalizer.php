<?php

namespace Morebec\Orkestra\Messaging\Normalization;

use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizerInterface;
use Morebec\Orkestra\Normalization\Normalizer\NormalizerInterface;
use Morebec\Orkestra\Normalization\ObjectNormalizer;
use Morebec\Orkestra\Normalization\ObjectNormalizerInterface;

/**
 * Implementation of a {@link MessageNormalizer} that relies on a class map
 * (mapping between classes and message type names) to denormalize and normalize messages.
 */
class ClassMapMessageNormalizer implements MessageNormalizerInterface
{
    /**
     * @var MessageClassMapInterface
     */
    private $classMap;
    /**
     * @var ObjectNormalizerInterface|null
     */
    private $objectNormalizer;

    public function __construct(MessageClassMapInterface $classMap, ?ObjectNormalizerInterface $objectNormalizer = null)
    {
        $this->classMap = $classMap;
        $this->objectNormalizer = $objectNormalizer ?: new ObjectNormalizer();
    }

    public function normalize(MessageInterface $message): ?array
    {
        $data = $this->objectNormalizer->normalize($message);
        $data['messageTypeName'] = $message::getTypeName();

        return $data;
    }

    public function denormalize(?array $data, ?string $messageTypeName = null): ?MessageInterface
    {
        $messageTypeName = $messageTypeName ?: $data['messageTypeName'];

        $className = $this->classMap->getClassNameForMessageTypeName($messageTypeName);
        if (!$className) {
            // Common errors: the class map does not contain the data, there is a typo in the messageTypeName.
            throw new \InvalidArgumentException(sprintf('Could not find a Class Name for Message "%s". Did you add it to the MessageClassMapInterface?', $messageTypeName));
        }

        return $this->objectNormalizer->denormalize($data, $className);
    }

    /**
     * Adds a Normalizer to the internal object normalizer.
     */
    public function addNormalizer(NormalizerInterface $normalizer): void
    {
        $this->objectNormalizer->addNormalizer($normalizer);
    }

    /**
     * Adds a Normalizer to the internal object normalizer.
     */
    public function addDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->objectNormalizer->addDenormalizer($denormalizer);
    }
}
