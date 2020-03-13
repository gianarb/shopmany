<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\Types;

use ArrayIterator;
use IteratorAggregate;
use phpDocumentor\Reflection\Type;
use function array_key_exists;
use function implode;

/**
 * Value Object representing a Compound Type.
 *
 * A Compound Type is not so much a special keyword or object reference but is a series of Types that are separated
 * using an OR operator (`|`). This combination of types signifies that whatever is associated with this compound type
 * may contain a value with any of the given types.
 *
 * @template-implements IteratorAggregate<int, Type>
 */
final class Compound implements Type, IteratorAggregate
{
    /** @var array<int, Type> */
    private $types = [];

    /**
     * Initializes a compound type (i.e. `string|int`) and tests if the provided types all implement the Type interface.
     *
     * @param Type[] $types
     *
     * @phpstan-param list<Type> $types
     */
    public function __construct(array $types)
    {
        foreach ($types as $type) {
            $this->add($type);
        }
    }

    /**
     * Returns the type at the given index.
     */
    public function get(int $index) : ?Type
    {
        if (!$this->has($index)) {
            return null;
        }

        return $this->types[$index];
    }

    /**
     * Tests if this compound type has a type with the given index.
     */
    public function has(int $index) : bool
    {
        return array_key_exists($index, $this->types);
    }

    /**
     * Tests if this compound type contains the given type.
     */
    public function contains(Type $type) : bool
    {
        foreach ($this->types as $typePart) {
            // if the type is duplicate; do not add it
            if ((string) $typePart === (string) $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a rendered output of the Type as it would be used in a DocBlock.
     */
    public function __toString() : string
    {
        return implode('|', $this->types);
    }

    /**
     * @return ArrayIterator<int, Type>
     */
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->types);
    }

    private function add(Type $type) : void
    {
        // if the type is duplicate; do not add it
        if ($this->contains($type)) {
            return;
        }

        $this->types[] = $type;
    }
}
