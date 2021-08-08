<?php

namespace Morebec\Orkestra\PostgreSqlDocumentStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Morebec\Orkestra\DateTime\Date;
use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\PostgreSqlDocumentStore\Filter\Criterion;
use Morebec\Orkestra\PostgreSqlDocumentStore\Filter\Filter;
use Morebec\Orkestra\PostgreSqlDocumentStore\Filter\FilterOperator;

/**
 * Takes a Filter and a DBAL query builder and configures the latter with the former.
 *
 * @internal
 */
final class FilterQueryBuilderConfigurator
{
    /**
     * Configures a Query Builder according to a Filter.
     */
    public function configure(Filter $filter, QueryBuilder $qb): QueryBuilder
    {
        $ands = $filter->getAndCriteria();
        $ors = $filter->getOrCriteria();

        foreach ($ands as $criterion) {
            $qb->andWhere($this->convertCriterionToSql($criterion, $qb));
        }

        foreach ($ors as $criterion) {
            $qb->orWhere($this->convertCriterionToSql($criterion, $qb));
        }

        return $qb;
    }

    private function convertCriterionFieldToSql(Criterion $criterion): string
    {
        $field = $criterion->getField();
        // We need to make it so that:
        // 'field.nested.childField becomes data->nested->>childField.
        // If it is created at, updated at, or ID we will do it on the columns directly.
        if (!\in_array($field, [CollectionTableColumnKeys::ID, CollectionTableColumnKeys::CREATED_AT, CollectionTableColumnKeys::UPDATED_AT], true)) {
            // Explode . into an array
            $parts = explode('.', $field);

            // Quote field names.
            $parts = array_map(static function (string $part) {
                return "'$part'";
            }, $parts);

            // We add the data selector
            array_unshift($parts, 'data');

            // Implode parts so they are glued with ->
            $field = implode('->', $parts);

            // Replace last -> by ->> to access JSON as text for easier comparison.
            $pos = strrpos($field, '->');
            if ($pos !== false) {
                $field = substr_replace($field, '->>', $pos, \strlen('->'));
            }
        }

        // Apply cast
        $cast = $criterion->getCast();
        if ($cast) {
            $field = "($field)::$cast";
        }

        return $field;
    }

    private function convertCriterionValueToSql(Criterion $criterion)
    {
        // Auto cast some types
        $value = $criterion->getValue();

        /*if (\is_string($value)) {
            //$value = sprintf("'%s'", $value);
        } else*/
        if ($value === null) {
            $value = 'NULL';
        } elseif ($value instanceof DateTime || $value instanceof Date) {
            $value = ($value->toAtomString());
        }

        return $value;
    }

    private function convertCriterionOperatorToSql(Criterion $criterion): string
    {
        $operator = $criterion->getOperator();

        // Support for NULL and EQUAL operator.
        if ($criterion->getValue() === null && $operator->isEqualTo(FilterOperator::EQUAL())) {
            $operator = FilterOperator::IS();
        }

        $stringOperator = (string) $operator;

        return str_replace('_', ' ', $stringOperator);
    }

    private function convertCriterionToSql(Criterion $criterion, QueryBuilder $qb): string
    {
        $field = $this->convertCriterionFieldToSql($criterion);
        $operator = $this->convertCriterionOperatorToSql($criterion);

        $criterionValue = $criterion->getValue();
        if (\is_array($criterionValue)) {
            $type = Connection::PARAM_STR_ARRAY;
        } elseif (\is_bool($criterionValue)) {
            $type = ParameterType::BOOLEAN;
        } elseif (\is_int($criterionValue)) {
            $type = ParameterType::INTEGER;
        } elseif ($criterionValue === null) {
            $type = ParameterType::NULL;
        } else {
            $type = ParameterType::STRING;
        }

        // There seem to be a problem when the value is null in a positional argument.
        $value = $criterionValue === null ? 'NULL' :
            $qb->createPositionalParameter($this->convertCriterionValueToSql($criterion), $type)
        ;

        if (\is_array($criterionValue)) {
            $value = "($value)";
        }

        return "$field $operator $value";
    }
}
