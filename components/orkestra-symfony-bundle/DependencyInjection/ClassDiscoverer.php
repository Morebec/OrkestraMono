<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection;

use Symfony\Component\Finder\Finder;

/**
 * The class discoverer discovers classes recursively from a directory and
 * provides the whole list of declared classes as a string.
 * This allows different services to be able to handle types dynamically.
 */
class ClassDiscoverer
{
    /**
     * Discovers the classes in a given directory.
     *
     * @return iterable|string[]
     */
    public static function discover(string $directory): iterable
    {
        $files = Finder::create()->in($directory)->files()->name('*.php');

        foreach ($files as $file) {
            require_once $file->getRealPath();
        }

        return get_declared_classes();
    }
}
