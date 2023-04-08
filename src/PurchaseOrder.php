<?php

declare(strict_types=1);

namespace Yarikul;

class PurchaseOrder implements \JsonSerializable
{
    public function __construct(
        public readonly \DateTimeImmutable $date,
        public readonly ?string $supplier,
        public readonly ?string $orderNumber,
        public readonly ?string $product,
        public readonly float $pricePerTonne,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'date' => $this->date->format('Y-m-d'),
            'supplier' => $this->supplier,
            'orderNumber' => $this->orderNumber,
            'product' => $this->product,
            'pricePerTonne' => $this->pricePerTonne,
        ];
    }
}
