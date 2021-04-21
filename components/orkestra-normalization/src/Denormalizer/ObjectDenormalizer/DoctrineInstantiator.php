<?php

namespace Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer;

use Doctrine\Instantiator\Exception\ExceptionInterface;
use Doctrine\Instantiator\Instantiator;

/**
 * Class Instantiator based off Doctrine Instantiator.
 */
class DoctrineInstantiator implements ClassInstantiatorInterface
{
    /**
     * @var Instantiator
     */
    private $instantiator;

    /**
     * DoctrineInstantiator constructor.
     */
    public function __construct()
    {
        $this->instantiator = new Instantiator();
    }

    /**
     * @throws ExceptionInterface
     */
    public function instantiate(string $className): object
    {
        return $this->instantiator->instantiate($className);
    }
}
