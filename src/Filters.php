<?php

declare(strict_types=1);

namespace Yarikul;

/**
 * @implements \IteratorAggregate<string, string|null>
 */
class Filters implements \IteratorAggregate
{
    public function __construct(
        public readonly ?string $supplier,
        public readonly ?string $product,
        public readonly ?string $year,
    ) {
    }

    public function getIterator(): \Traversable
    {
        yield 'supplier' => $this->supplier;
        yield 'product' => $this->product;
        yield 'year' => $this->year;
    }
}
