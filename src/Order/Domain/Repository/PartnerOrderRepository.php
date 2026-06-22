<?php

namespace App\Order\Domain\Repository;

use App\Order\Domain\Entity\PartnerOrder;

interface PartnerOrderRepository
{
    public function save(PartnerOrder $order): void;

    public function findByPartnerAndOrderId(string $partnerId, string $orderId): ?PartnerOrder;
}
