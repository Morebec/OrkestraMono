<?php

namespace Morebec\Orkestra\PostgreSqlDocumentStore\Filter;

use Morebec\Orkestra\Enum\Enum;

/**
 * @method static self EQUAL()
 * @method static self NOT_EQUAL()
 * @method static self LESS_THAN()
 * @method static self GREATER_THAN()
 * @method static self LESS_OR_EQUAL()
 * @method static self GREATER_OR_EQUAL()
 * @method static self IS()
 * @method static self IS_NOT()
 * @method static self BETWEEN()
 * @method static self NOT_BETWEEN()
 * @method static self LIKE()
 */
class FilterOperator extends Enum
{
    /** @var string */
    public const EQUAL = '=';

    public const NOT_EQUAL = '!==';

    public const LESS_THAN = '<';
    public const GREATER_THAN = '>';

    public const LESS_OR_EQUAL = '<=';
    public const GREATER_OR_EQUAL = '>=';

    public const IS = 'IS';
    public const IS_NOT = 'IS NOT';
    public const NOT_BETWEEN = 'NOT BETWEEN';

    public const LIKE = 'LIKE';
    public const NOT_LIKE = 'NOT LIKE';
}
