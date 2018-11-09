<?php declare(strict_types=1);

namespace Clue\JsonQuery;

/**
 * Trait QueryExpressionUtilities
 * @package Clue\JsonQuery
 */
trait QueryExpressionUtilities
{
    protected function isObject($value): bool
    {
        return (is_object($value) || (is_array($value) && ($value === [] || !isset($value[0]))));
    }

    protected function isVector($value): bool
    {
        return ($value === [] || (is_array($value) && isset($value[0])));
    }

    protected function errValue($in): string
    {
        return is_scalar($in) && !is_bool($in) ? (string)$in : (is_object($in) ? get_class($in) : gettype($in));
    }
}