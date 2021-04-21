<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection;

use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageClassMap;
use Morebec\Orkestra\Messaging\Normalization\MessageClassMapInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Factory Responsible for instantiating the {@link MessageClassMap}.
 * It uses reflection to determine the type names and the classes to use.
 * It saves this in Symfony's cache for performance.
 * Essentially
 * 0. If cache is available goes to step 4.
 * 1. It finds by looking at all the classes of the project if they are {@link MessageInterface}.
 * 2. With this list of messages it builds a mapping between the classname and the message type name
 * 3. It saves this to cache.
 * 4. It builds a {@link MessageClassMapInterface} from these cached values.
 */
class SymfonyMessageClassMapFactory
{
    private const CACHE_KEY = 'message_class_map';

    /**
     * Project's source directory.
     *
     * @var string
     */
    private $sourceDir;

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(ParameterBagInterface $parameterBag, CacheInterface $cache)
    {
        $this->sourceDir = $parameterBag->get('kernel.project_dir').'/src/';
        $this->cache = $cache;
    }

    /**
     * Generates the registry.
     */
    public function buildClassMap(): MessageClassMapInterface
    {
        $sourceDir = $this->sourceDir;
        $map = $this->cache->get(self::CACHE_KEY, static function (ItemInterface $item) use ($sourceDir) {
            $item->expiresAfter(null);
            $classes = ClassDiscoverer::discover($sourceDir);

            $map = [];
            foreach ($classes as $class) {
                if (is_a($class, MessageInterface::class, true)) {
                    $r = new \ReflectionClass($class);
                    if ($r->isAbstract() || $r->isInterface()) {
                        continue;
                    }
                    $typeName = $class::getTypeName();
                    $map[$typeName] = $class;
                }
            }

            return $map;
        });

        return new MessageClassMap($map);
    }
}
