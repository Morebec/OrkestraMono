<?php

namespace Tests\Morebec\Orkestra\Collections;

use Morebec\Orkestra\Collections\TypedCollection;
use PHPUnit\Framework\TestCase;

class TypedCollectionTest extends TestCase
{
    public function testPrepend(): void
    {
        $collection = new TypedCollection(self::class);
        $collection->prepend($this);

        $this->expectException(\InvalidArgumentException::class);
        $collection->prepend(5);
    }

    public function testAdd(): void
    {
        $collection = new TypedCollection(self::class);
        $collection->add($this);

        $this->expectException(\InvalidArgumentException::class);
        $collection->add(5);
    }
}
