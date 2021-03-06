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
    private string $cacheDirectory;

    private ObjectNormalizer $normalizer;

    public function __construct(string $cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;
        $this->normalizer = new ObjectNormalizer();

        $this->normalizer->addDenormalizer(new class() implements DenormalizerInterface {
            public function denormalize(DenormalizationContextInterface $context): MessageRoute
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
     *
     * @throws \JsonException
     */
    public function dumpRoutes(MessageRouteCollection $routes): void
    {
        // Normalize routes
        $data = json_encode($this->normalizer->normalize($routes->toArray()), \JSON_THROW_ON_ERROR);
        file_put_contents($this->getCacheFile(), $data);
    }

    /**
     * Loads the routes back from the cache.
     *
     * @throws \JsonException
     */
    public function loadRoutes(): MessageRouteCollection
    {
        $cacheFile = $this->getCacheFile();
        if (!file_exists($cacheFile)) {
            return new MessageRouteCollection([]);
        }

        $data = json_decode(file_get_contents($cacheFile), true, 512, \JSON_THROW_ON_ERROR);
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
