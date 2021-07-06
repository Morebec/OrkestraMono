<?php

namespace Tests\Morebec\OrkestraSymfonyBundle\DependencyInjection;

use Morebec\Orkestra\SymfonyBundle\DependencyInjection\ClassDiscoverer;
use PHPUnit\Framework\TestCase;

class ClassDiscovererTest extends TestCase
{
    public function testDiscover(): void
    {
        $discovered = ClassDiscoverer::discover(__DIR__);

        self::assertContains(self::class, $discovered);
    }
}
