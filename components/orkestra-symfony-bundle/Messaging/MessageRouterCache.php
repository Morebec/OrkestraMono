<?php

namespace Morebec\Orkestra\SymfonyBundle\Messaging;

use Morebec\Orkestra\Messaging\Routing\MessageRoute;
use Morebec\Orkestra\Messaging\Routing\MessageRouteCollection;
use Morebec\Orkestra\Messaging\Routing\MessageRouteInterface;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContextInterface;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizerInterface;
use Morebec\Orkestra\Normalization\ObjectNormalizer;
use Psr\Container\ContainerInterface;

/**
 * Service responsible for saving the routes of the {@link MessageRouterInterface}
 * in symfony's cache at the {@link ContainerInterface}'s compile time.
 * To avoid reevaluating the route definitions on every request.
 */
class MessageRouterCache
{
    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * @var ObjectNormalizer
     */
    private $normalizer;

    public function __construct(string $cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;
        $this->normalizer = new ObjectNormalizer();

        $this->normalizer->addDenormalizer(new class() implements DenormalizerInterface {
            public function denormalize(DenormalizationContextInterface $context)
            {
                $data = $context->getValue();

                return new MessageRoute(
                    $data['messageTypeName'],
                    $data['messageHandlerClassName'],
                    $data['messageHandlerMethodName']
                );
            }

            public function supports(DenormalizationContextInterface $context): bool
            {
                return $context->getTypeName() === MessageRouteInterface::class;
            }
        });
    }

    /**
     * Dumps a set of routes to the cache.
     */
    public function dumpRoutes(MessageRouteCollection $routes): void
    {
        // Normalize routes
        $data = json_encode($this->normalizer->normalize($routes->toArray())/*, JSON_PRETTY_PRINT*/);
        file_put_contents($this->getCacheFile(), $data);
    }

    /**
     * Loads the routes back from the cache.
     */
    public function loadRoutes(): MessageRouteCollection
    {
        $data = json_decode(file_get_contents($this->getCacheFile()), true);
        $routes = [];
        foreach ($data as $datum) {
            $routes[] = $this->normalizer->denormalize($datum, MessageRouteInterface::class);
        }

        return new MessageRouteCollection($routes);
    }

    protected function getCacheFile(): string
    {
        return $this->cacheDirectory.'/message_routes.json';
    }
}
