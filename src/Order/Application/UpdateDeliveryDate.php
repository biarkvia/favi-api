<?php

namespace App\Order\Application;

use DateTimeImmutable;

readonly class UpdateDeliveryDate
{
    public function __construct(
        public string $partnerId,
        public string $orderId,
        public DateTimeImmutable $expectedDeliveryDate,
    ) {}
}
