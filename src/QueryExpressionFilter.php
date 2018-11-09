<?php declare(strict_types=1);

namespace Clue\JsonQuery;

use DomainException;

class QueryExpressionFilter implements Filter
{
    private const DEFAULT_SELECTOR_SEPARATOR = '.';

    private $selectorSeparator = self::DEFAULT_SELECTOR_SEPARATOR;

    private $queryExpression;

    /** @var \Clue\JsonQuery\Comparators */
    private $comparators;

    public function __construct($queryExpression, $comparators = null)
    {
        $this->queryExpression = $queryExpression;

        if (is_string($comparators)) {
            if (!class_exists($comparators, true)) {
                throw new \InvalidArgumentException("Provided Comparator class \"{$comparators}\" or is not auto-loadable");
            }
            $comparators = new $comparators;
        } elseif (null === $comparators) {
            $comparators = new DefaultComparators();
        }

        if (!is_object($comparators) || !($comparators instanceof Comparators)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 2 must be instance of %1$s, name of class implementing %1$s, or null to use %2$s.  %3$s provided.',
                Comparators::class,
                DefaultComparators::class,
                is_scalar($comparators) ? (string)$comparators :
                    (is_object($comparators) ? get_class($comparators) : gettype($comparators))
            ));
        }
        $this->comparators = $comparators;
    }

    public function doesMatch($data): bool
    {
        return $this->matchFilter($data, $this->queryExpression);
    }

    private function matchFilter($data, $filter): bool
    {
        if ($this->isObject($filter)) {
            return $this->matchAnd($data, $filter);
        } else {
            throw new DomainException('Invalid filter type');
        }
    }

    private function matchOr($data, $filter): bool
    {
        if ($this->isVector($filter)) {
            foreach ($filter as $element) {
                if ($this->matchFilter($data, $element)) {
                    return true;
                }
            }
        } elseif ($this->isObject($filter)) {
            foreach ($filter as $key => $value) {
                if ($this->matchValue($data, $key, $value)) {
                    return true;
                }
            }
        } else {
            throw new DomainException('Invalid data type for $or combinator');
        }

        return false;
    }

    private function matchAnd($data, $filter): bool
    {
        if ($this->isVector($filter)) {
            foreach ($filter as $element) {
                if (!$this->matchFilter($data, $element)) {
                    return false;
                }
            }
        } elseif ($this->isObject($filter)) {
            foreach ($filter as $key => $value) {
                if (!$this->matchValue($data, $key, $value)) {
                    return false;
                }
            }
        } else {
            throw new DomainException('Invalid data type for $and combinator');
        }

        return true;
    }

    private function matchValue($data, string $column, $expectation): bool
    {
        if ($column === '$and') {
            return $this->matchAnd($data, $expectation);
        } elseif ($column === '$or') {
            return $this->matchOr($data, $expectation);
        } elseif ($column === '$not') {
            return !$this->matchAnd($data, $expectation);
        } elseif ($column[0] === '!') {
            return !$this->matchValue($data, substr($column, 1), $expectation);
        } elseif ($column[0] === '$') {
            return $this->matchComparator($data, $column, $expectation);
        }

        if ($this->isVector($expectation)) {
            // L2 simple list matching
            $expectation = ['$in' => $expectation];
        } elseif (!$this->isObject($expectation)) {
            // L2 simple scalar matching
            $expectation = ['$is' => $expectation];
        }

        $actualValue = $this->fetchValue($data, $column);

        foreach ($expectation as $comparator => $expectedValue) {
            $ret = $this->matchComparator($actualValue, $comparator, $expectedValue);

            if (!$ret) {
                return false;
            }
        }

        return true;
    }

    private function fetchValue($data, $column)
    {
        $path = explode($this->selectorSeparator, $column);

        foreach ($path as $field) {
            if (is_array($data) && isset($data[$field])) {
                $data = $data[$field];
            } elseif (is_object($data) && isset($data->$field)) {
                $data = $data->$field;
            } else {
                return null;
            }
        }

        return $data;
    }

    /** @internal */
    public function matchComparator($actualValue, string $comparator, $expectedValue): bool
    {
        $negate = false;
        while ($comparator[0] === '!') {
            $negate = !$negate;
            $comparator = substr($comparator, 1);
        }

        if (!isset($this->comparators[$comparator])) {
            throw new DomainException('Unknown comparator "' . $comparator . '" given');
        }

        return $this->comparators[$comparator]($actualValue, $expectedValue) XOR $negate;
    }

    /** @internal */
    public function isObject($value): bool
    {
        return (is_object($value) || (is_array($value) && ($value === [] || !isset($value[0]))));
    }

    /** @internal */
    public function isVector($value): bool
    {
        return ($value === [] || (is_array($value) && isset($value[0])));
    }
}
