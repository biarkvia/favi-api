<?php

namespace App\Order\Application;

use DateTimeImmutable;

readonly class CreatePartnerOrder
{
    /**
     * @param CreatePartnerOrderItem[] $items
     */
    public function __construct(
        public string $partnerId,
        public string $orderId,
        public DateTimeImmutable $expectedDeliveryDate,
        public string $totalValue,
        public array $items,
        public array $rawPayload,
    ) {}
}
