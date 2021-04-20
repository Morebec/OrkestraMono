<?php

namespace Tests\Morebec\Orkestra\Enum;

use Morebec\Orkestra\Enum\Enum;

/**
 * Fake Enum class used to test enums.
 *
 * @method static self NAME()
 * @method static self VALUE()
 */
class FakeEnum extends Enum
{
    public const NAME = 'NAME_VALUE';

    public const VALUE = 'VALUE';
}
