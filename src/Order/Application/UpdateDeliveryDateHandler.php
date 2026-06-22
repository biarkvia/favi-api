<?php

namespace App\Order\Application;

use App\Order\Domain\Entity\PartnerOrder;
use App\Order\Domain\Exception\PartnerOrderNotFoundException;
use App\Order\Domain\Repository\PartnerOrderRepository;

readonly class UpdateDeliveryDateHandler
{
    public function __construct(private PartnerOrderRepository $partnerOrderRepository) {}

    public function handle(UpdateDeliveryDate $command): PartnerOrder
    {
        $order = $this->partnerOrderRepository->findByPartnerAndOrderId(
            $command->partnerId,
            $command->orderId,
        );

        if (!$order) {
            throw new PartnerOrderNotFoundException($command->partnerId, $command->orderId);
        }

        $order->changeExpectedDeliveryDate($command->expectedDeliveryDate);
        $this->partnerOrderRepository->save($order);

        return $order;
    }
}
