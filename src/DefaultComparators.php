<?php declare(strict_types=1);

namespace Clue\JsonQuery;

class DefaultComparators implements Comparators
{
    use QueryExpressionUtilities;

    // default comparators
    private const IS       = '$is';
    private const IN       = '$in';
    private const CONTAINS = '$contains';
    private const LT       = '$lt';
    private const LTE      = '$lte';
    private const GT       = '$gt';
    private const GTE      = '$gte';
    private const NOT      = '$not';

    private const LIST     = [
        self::IS       => self::IS,
        self::IN       => self::IN,
        self::CONTAINS => self::CONTAINS,
        self::LT       => self::LT,
        self::LTE      => self::LTE,
        self::GT       => self::GT,
        self::GTE      => self::GTE,
        self::NOT      => self::NOT,
    ];

    public function list(): array
    {
        return self::LIST;
    }

    public function can(string $comparator): bool
    {
        return isset(self::LIST[$comparator]);
    }

    public function compare(string $comparator, $data, $expectation): ?bool
    {
        if (self::IS === $comparator) {
            return $this->is($data, $expectation);
        } elseif (self::IN === $comparator) {
            return $this->in($data, $expectation);
        } elseif (self::CONTAINS === $comparator) {
            return $this->contains($data, $expectation);
        } elseif (self::LT === $comparator) {
            return $this->lt($data, $expectation);
        } elseif (self::LTE === $comparator) {
            return $this->lte($data, $expectation);
        } elseif (self::GT === $comparator) {
            return $this->gt($data, $expectation);
        } elseif (self::GTE === $comparator) {
            return $this->gte($data, $expectation);
        } elseif (self::NOT === $comparator) {
            return $this->not($data, $expectation);
        } else {
            return null;
        }
    }

    private function is($actualValue, $expectedValue): bool
    {
        return ($actualValue === $expectedValue);
    }

    private function in($actualValue, $expectedValue): bool
    {
        return in_array($actualValue, $expectedValue, true);
    }

    private function contains($actualValue, $expectedValue)
    {
        if ($this->isObject($actualValue)) {
            if (is_object($actualValue)) {
                return property_exists($actualValue, $expectedValue);
            } else {
                return array_key_exists($expectedValue, $actualValue);
            }
        } elseif ($this->isVector($actualValue)) {
            return in_array($expectedValue, $actualValue, true);
        } else {
            return (strpos($actualValue, $expectedValue) !== false);
        }
    }

    private function lt($actualValue, $expectedValue): bool
    {
        return ($actualValue < $expectedValue);
    }

    private function lte($actualValue, $expectedValue): bool
    {
        return ($actualValue <= $expectedValue);
    }

    private function gt($actualValue, $expectedValue): bool
    {
        return ($actualValue > $expectedValue);
    }

    private function gte($actualValue, $expectedValue): bool
    {
        return ($actualValue >= $expectedValue);
    }

    private function matchComparator($actualValue, string $comparator, $expectedValue): ?bool
    {
        $negate = false;
        while ($comparator[0] === '!') {
            $negate = !$negate;
            $comparator = substr($comparator, 1);
        }

        if (!isset($this->comparators[$comparator])) {
            return null;
        }

        return $this->compare($comparator, $actualValue, $expectedValue);
    }

    private function not($actualValue, $expectedValue)
    {
        return !$this->matchComparator($actualValue,
            $this->isVector($expectedValue) ? '$in' : '$is',
            $expectedValue);
    }
}