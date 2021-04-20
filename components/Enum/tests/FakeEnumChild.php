<?php

namespace Tests\Morebec\Orkestra\Enum;

/**
 * Extends a Parent FakeEnum to allow testing for constant inheritance.
 */
class FakeEnumChild extends FakeEnum
{
    public const CHILD_SPECIFIC = 'CHILD_SPECIFIC';
}
