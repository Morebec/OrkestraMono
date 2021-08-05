<?php

namespace Morebec\Orkestra\SymfonyBundle\Web;

use Morebec\Orkestra\Normalization\ObjectNormalizerInterface;

/**
 * This type of object normalizer is capable of normalizing objects specifically for
 * http transport.
 */
interface HttpObjectNormalizerInterface extends ObjectNormalizerInterface
{
}
