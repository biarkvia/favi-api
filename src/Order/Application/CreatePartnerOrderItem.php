<?php

namespace App\Order\Application;

readonly class CreatePartnerOrderItem
{
    public function __construct(
        public string $productId,
        public string $name,
        public string $price,
        public int $quantity,
    ) {}
}
