<?php declare(strict_types=1);

namespace Clue\JsonQuery;

/**
 * Class QueryExpressionParserError
 * @package Clue\JsonQuery
 */
class ParserError implements \JsonSerializable
{
    private $message;
    private $errata;

    public function __construct(string $message, $errata)
    {
        $this->message = $message;
        $this->errata = is_object($errata) ? clone $errata : $errata;
    }

    public function jsonSerialize()
    {
        return [
            'message' => $this->message,
            'errata'  => $this->errata,
        ];
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->message;
    }
}