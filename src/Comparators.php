<?php declare(strict_types=1);

namespace Clue\JsonQuery;

/**
 * Interface Comparators
 * @package Clue\JsonQuery
 */
interface Comparators
{
    /**
     * Must return an array string of the different comparators handled by this instance
     *
     * @return string[]
     */
    public function list(): array;

    /**
     * Must return false if the requested comparator is not present on this instance
     *
     * @param string $comparator
     * @return bool
     */
    public function can(string $comparator): bool;

    /**
     * Must compare the provided data with the expected input if this instance has the specified operation.  Otherwise,
     * must return null
     *
     * @param string $comparator
     * @param mixed $data Value from input
     * @param mixed $expectation Expected value
     * @return null|bool
     */
    public function compare(string $comparator, $data, $expectation): ?bool;
}