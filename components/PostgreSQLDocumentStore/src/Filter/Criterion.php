<?php

namespace Morebec\Orkestra\PostgreSqlDocumentStore\Filter;

use Morebec\Orkestra\DateTime\Date;
use Morebec\Orkestra\DateTime\DateTime;

// TODO: Broken, could be a cool thing to fix.
class Criterion
{
    /** @var string field */
    private $field;

    /** @var FilterOperator */
    private $operator;

    /** @var mixed value */
    private $value;

    /**
     * @var string|null
     */
    private $cast;

    /**
     * Constructs a criterion.
     *
     * @param string         $field    name of the field to test
     * @param FilterOperator $operator operator
     * @param mixed          $value    expected value
     */
    public function __construct(string $field, FilterOperator $operator, $value = null, ?string $cast = null)
    {
        if (!$field) {
            throw new \InvalidArgumentException('A field cannot be blank');
        }

        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
        $this->cast = $cast;
    }

    public function __toString(): string
    {
        $field = $this->field;
        $value = $this->value;

        // Auto cast some types
        if ($this->value instanceof DateTime || $this->value instanceof Date) {
            $value = "'{$this->value->toAtomString()}'";
        } elseif (\is_string($this->value)) {
            $value = sprintf("'%s'", $this->value);
        } elseif ($this->value === null) {
            $value = 'NULL';
        }

        if ($this->cast) {
            $field = "($field)::$this->cast";
            $value = "$value::$this->cast";
        }

        return sprintf('%s %s %s', $field, $this->operator, $value);
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getOperator(): FilterOperator
    {
        return $this->operator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getCast(): ?string
    {
        return $this->cast;
    }
}
