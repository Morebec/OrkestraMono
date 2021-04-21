<?php

namespace Morebec\Orkestra\PostgreSqlDocumentStore\Filter;

class Filter
{
    /** @var array list of criteria */
    private $ands;

    /** @var array list of criteria */
    private $ors;

    /**
     * Constructs the filter instance
     * It uses two lists of queries:
     * - ands: list of all criteria a document must match to be part of the result
     * - ors:  list of criteria a document can match to override the ands criteria.
     *
     * @param array $ands mandatory queries
     * @param array $ors  optional queries
     */
    public function __construct(array $ands, array $ors = [])
    {
        $this->ands = $ands;
        $this->ors = $ors;
    }

    /**
     * Returns a string representation of the value object.
     */
    public function __toString(): string
    {
        $ands = implode(' AND ', $this->ands);
        $ors = implode(' OR ', $this->ors);

        if (!empty($ors)) {
            return implode(' OR ', [$ands, $ors]);
        }

        return $ands;
    }

    public static function where(string $fieldName, FilterOperator $operator, $value, ?string $cast = null): self
    {
        return new self([new Criterion($fieldName, $operator, $value, $cast)]);
    }

    /**
     * Creates a filter object with a single Criterion.
     */
    public static function findByField(
        string $fieldName,
        FilterOperator $operator,
        $value,
        ?string $cast = null
    ): self {
        return new self([new Criterion($fieldName, $operator, $value, $cast)]);
    }

    /**
     * Creates a find by id is equal to a certain value self object.
     *
     * @param string $id id of the document
     */
    public static function findById(string $id): self
    {
        return new self([
            new Criterion('id', FilterOperator::EQUAL(), $id),
        ]);
    }

    public function or(string $field, FilterOperator $operator, $value, ?string $cast = null): self
    {
        return $this->addOrCriterion(new Criterion($field, $operator, $value, $cast));
    }

    public function and(string $field, FilterOperator $operator, $value, ?string $cast = null): self
    {
        return $this->addAndCriterion(new Criterion($field, $operator, $value, $cast));
    }

    /**
     * Adds an Or criterion.
     *
     * @return $this
     */
    public function addOrCriterion(Criterion $criterion): self
    {
        $this->ors[] = $criterion;

        return $this;
    }

    /**
     * Adds an And criterion.
     *
     * @return $this
     */
    public function addAndCriterion(Criterion $criterion): self
    {
        $this->ands[] = $criterion;

        return $this;
    }

    /**
     * Returns the list of criteria used in this filter.
     *
     * @return Criterion[]
     */
    public function getCriteria(): array
    {
        return array_merge($this->ands, $this->ors);
    }

    /**
     * @return Criterion[]
     */
    public function getAndCriteria(): array
    {
        return $this->ands;
    }

    /**
     * @return Criterion[]
     */
    public function getOrCriteria(): array
    {
        return $this->ors;
    }

    public function isEqualTo(self $f): bool
    {
        return (string) $this == (string) $f;
    }
}
