<?php

namespace Morebec\Orkestra\Messaging\Normalization;

use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Service responsible for normalizing Messages.
 */
interface MessageNormalizerInterface
{
    /**
     * Normalizes a Message.
     */
    public function normalize(MessageInterface $message): ?array;

    /**
     * Denormalizes a Message.
     *
     * @param string|null $messageTypeName an optional messageType name in cases where the type is already known,
     *                                     otherwise it should be detected from the normalized data itself
     */
    public function denormalize(?array $data, ?string $messageTypeName = null): ?MessageInterface;
}
