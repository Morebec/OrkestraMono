<?php

namespace Tests\Morebec\Orkestra\Collections;

use Morebec\Orkestra\Collections\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testGet()
    {
        $john = [
            'name' => 'John',
            'phoneNumbers' => new Collection([
                '111-111-1111',
                '222-222-2222',
            ]),
        ];
        $jane = [
            'name' => 'Jane',
            'phoneNumbers' => new Collection([
                '333-333-3333',
                '444-444-4444',
            ]),
        ];
        $persons = new Collection([$john, $jane]);

        $this->assertEquals($john, $persons->get(0));
        $this->assertNotEquals($john, $persons->get(1));

        // Throws Undefined Offset error
        // $persons->get(2);
    }

    public function testAreAll()
    {
        $persons = new Collection([
            [
                'name' => 'John',
                'phoneNumbers' => new Collection([
                    '111-111-1111',
                    '222-222-2222',
                ]),
            ],
            [
                'name' => 'Jane',
                'phoneNumbers' => new Collection([
                    '333-333-3333',
                    '444-444-4444',
                ]),
            ],
        ]);

        $this->assertTrue($persons->areAll(static function ($person) {
            return !$person['phoneNumbers']->isEmpty();
        }));

        $this->assertFalse($persons->areAll(static function ($person) {
            return $person['phoneNumbers']->isEmpty();
        }));
    }

    public function testIsAny()
    {
        $persons = new Collection([
            [
                'name' => 'John',
                'phoneNumbers' => new Collection([
                    '111-111-1111',
                    '222-222-2222',
                ]),
            ],
            [
                'name' => 'Jane',
                'phoneNumbers' => new Collection([
                    '333-333-3333',
                    '444-444-4444',
                ]),
            ],
        ]);

        $this->assertTrue($persons->isAny(static function ($person) {
            return $person['name'] === 'Jane';
        }));

        $this->assertFalse($persons->isAny(static function ($person) {
            return $person['name'] === 'James';
        }));
    }

    public function testIsEmpty()
    {
        $this->assertTrue((new Collection([]))->isEmpty());
        $this->assertFalse((new Collection([1, 2, 3]))->isEmpty());
        $this->assertEmpty(new Collection());
        $this->assertNotEmpty(new Collection([1, 2, 3]));
    }

    public function testGetCount()
    {
        $persons = new Collection([
            [
                'name' => 'John',
                'phoneNumbers' => new Collection([
                    '111-111-1111',
                    '222-222-2222',
                ]),
            ],
            [
                'name' => 'Jane',
                'phoneNumbers' => new Collection([
                    '333-333-3333',
                    '444-444-4444',
                ]),
            ],
        ]);

        $this->assertCount(2, $persons);
        $this->assertEquals(2, $persons->getCount());
    }

    public function testMap()
    {
        $persons = new Collection([
            [
                'name' => 'John',
                'phoneNumbers' => new Collection([
                    '111-111-1111',
                    '222-222-2222',
                ]),
            ],
            [
                'name' => 'Jane',
                'phoneNumbers' => new Collection([
                    '333-333-3333',
                    '444-444-4444',
                ]),
            ],
        ]);

        // Returns a list of lists of phone numbers
        $names = $persons->map(static function ($person) {
            return $person['name'];
        });

        $this->assertEquals(['John', 'Jane'], $names->toArray());
    }

    public function testFlatten()
    {
        $persons = new Collection([
            [
                'name' => 'John',
                'phoneNumbers' => new Collection([
                    '111-111-1111',
                    '222-222-2222',
                ]),
            ],
            [
                'name' => 'Jane',
                'phoneNumbers' => new Collection([
                    '333-333-3333',
                    '444-444-4444',
                ]),
            ],
        ]);

        // Returns a list of lists of phone numbers
        $phoneNumberLists = $persons->map(static function ($person) {
            return $person['phoneNumbers'];
        });

        // Returns a flattened list of just the phone numbers
        $phoneNumbers = $phoneNumberLists->flatten();

        $this->assertEquals(['111-111-1111', '222-222-2222', '333-333-3333', '444-444-4444'], $phoneNumbers->toArray());
    }

    public function testReversed()
    {
        $c = new Collection([1, 2, 3]);

        $r = $c->reversed();

        $this->assertNotEquals($c, $r);

        $this->assertEquals(new Collection([3, 2, 1]), $r);
    }

    public function testSlice()
    {
        $c = new Collection([1, 2, 3, 'A', 'B', 'C']);

        $slices = $c->slice(3);

        // A, B, C
        $this->assertCount(3, $slices);
    }

    public function testChunk()
    {
        $c = new Collection([1, 2, 3, 4, 'A', 'B', 'C', 'D']);

        $chunks = $c->chunk(4);

        $this->assertCount(2, $chunks);
    }

    public function testReduce()
    {
        $c = new Collection([1, 2, 3, 4]);

        $sum = $c->reduce(static function ($carry, $item) {
            return $carry + $item;
        }, 0);

        $this->assertEquals(10, $sum);
    }

    public function testGetFirst()
    {
        $john = [
            'name' => 'John',
            'phoneNumbers' => new Collection([
                '111-111-1111',
                '222-222-2222',
            ]),
        ];
        $jane = [
            'name' => 'Jane',
            'phoneNumbers' => new Collection([
                '333-333-3333',
                '444-444-4444',
            ]),
        ];
        $persons = new Collection([$john, $jane]);

        $this->assertEquals($john, $persons->getFirst());

        // Undefined Index
        // (new Collection())->getFirst();
    }

    public function testFindFirstOrDefault()
    {
        $john = [
            'name' => 'John',
            'phoneNumbers' => new Collection([
                '111-111-1111',
                '222-222-2222',
            ]),
        ];
        $jane = [
            'name' => 'Jane',
            'phoneNumbers' => new Collection([
                '333-333-3333',
                '444-444-4444',
            ]),
        ];
        $persons = new Collection([$john, $jane]);

        $this->assertEquals($john, $persons->findFirstOrDefault(static function ($person) {
            return $person['name'] === 'John';
        }));

        $this->assertNull($persons->findFirstOrDefault(static function ($person) {
            return $person['name'] === 'Jimi';
        }));
    }

    public function testAdd()
    {
        $collection = new Collection();

        $collection->add(2);

        $this->assertCount(1, $collection);
    }

    public function testClear()
    {
        $collection = new Collection();

        $collection->add(2);

        $collection->clear();

        $this->assertEmpty($collection);
    }

    public function testGetLast()
    {
        $john = [
            'name' => 'John',
            'phoneNumbers' => new Collection([
                '111-111-1111',
                '222-222-2222',
            ]),
        ];
        $jane = [
            'name' => 'Jane',
            'phoneNumbers' => new Collection([
                '333-333-3333',
                '444-444-4444',
            ]),
        ];
        $persons = new Collection([$john, $jane]);

        $this->assertEquals($jane, $persons->getLast());
    }

    public function testCopy()
    {
        $john = [
            'name' => 'John',
            'phoneNumbers' => new Collection([
                '111-111-1111',
                '222-222-2222',
            ]),
        ];
        $jane = [
            'name' => 'Jane',
            'phoneNumbers' => new Collection([
                '333-333-3333',
                '444-444-4444',
            ]),
        ];
        $persons = new Collection([$john, $jane]);

        $copy = $persons->copy();

        $this->assertEquals($persons, $copy);
    }

    public function testFilter()
    {
        $john = [
            'name' => 'John',
            'phoneNumbers' => new Collection([
                '111-111-1111',
                '222-222-2222',
            ]),
        ];
        $jane = [
            'name' => 'Jane',
            'phoneNumbers' => new Collection([
                '333-333-3333',
                '444-444-4444',
            ]),
        ];
        $persons = new Collection([$john, $jane]);

        $filtered = $persons->filter(static function ($person) {
            return $person['name'] === 'Jane';
        });

        $this->assertCount(1, $filtered);

        $this->assertEquals('Jane', $filtered->getFirst()['name']);
    }

    public function testGetOrDefault()
    {
        $collection = new Collection();

        $value = $collection->getOrDefault(0, 'hello');

        $this->assertEquals('hello', $value);
    }

    public function testToArray()
    {
        $john = [
            'name' => 'John',
            'phoneNumbers' => new Collection([
                '111-111-1111',
                '222-222-2222',
            ]),
        ];
        $jane = [
            'name' => 'Jane',
            'phoneNumbers' => new Collection([
                '333-333-3333',
                '444-444-4444',
            ]),
        ];
        $persons = new Collection([$john, $jane]);

        $this->assertEquals([$john, $jane], $persons->toArray());
    }

    public function testGroupBy(): void
    {
        $values = [
            ['name' => 'orange', 'kind' => 'fruit'],
            ['name' => 'apple', 'kind' => 'fruit'],
            ['name' => 'celery', 'kind' => 'vegetable'],
            ['name' => 'carrot', 'kind' => 'vegetable'],
        ];

        $fruitsAndVegetables = (new Collection($values))->groupBy(static function ($key, $value) {
            return $value['kind'];
        });

        $this->assertCount(2, $fruitsAndVegetables);

        $this->assertCount(2, $fruitsAndVegetables->get('fruit'));
        $this->assertCount(2, $fruitsAndVegetables->get('vegetable'));
    }
}
