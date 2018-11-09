<?php declare(strict_types=1);

namespace Clue\JsonQuery;

/**
 * Class Comparison
 * @package Clue\JsonQuery
 */
class Comparison
{
    private $comparator;

    private $value;

    public function __construct($comparator, $value)
    {
        $this->comparator = $comparator;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getComparator()
    {
        return $this->comparator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}