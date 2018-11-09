<?php declare(strict_types=1);

namespace Clue\JsonQuery;

use PHPUnit\ExampleExtension\Comparator;

/**
 * Class QueryExpressionParser
 * @package Clue\JsonQuery
 */
class QueryExpressionParser
{
    use QueryExpressionUtilities;

    private $comparator;

    public function __construct(Comparator $comparator)
    {
        $this->comparator = $comparator;
    }

    /**
     * @param $data
     * @return array Returns array of length 2 where the first value is the root stanza, and all other
     */
    private function parseData($data, string $root = ): array
    {
        $stanza = null;
        $errors = [];
        if (!$this->isObject($data)) {
            $errors[] = new ParserError('Root data is not an object', $data);
            goto overAndOut;
        }

        overAndOut:
        return [$stanza, $errors];
    }
}