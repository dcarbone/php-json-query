<?php declare(strict_types=1);

namespace Clue\JsonQuery;

class Stanza
{
    use QueryExpressionUtilities;

    const AND = '$and';
    const OR  = '$or';

    private $type = self:: AND;

    private $components = [];

    public function __construct($data, string $type = self:: AND)
    {
        $this->type = $type;
    }

    public function addComponent($component): Stanza
    {
        if ($component instanceof Comparison || $component instanceof Stanza) {
            $this->components[] = $component;
            return $this;
        }
        throw new \InvalidArgumentException(sprintf(
            'Argument 1 must be instance of %s or %s, %s seen.',
            Comparison::class,
            Stanza::class,
            $this->errValue($component)
        ));
    }
}