<?php

namespace App\Order\Application;

use App\Order\Domain\Entity\PartnerOrder;
use App\Order\Domain\Entity\PartnerOrderItem;
use App\Order\Domain\Exception\PartnerOrderAlreadyExistsException;
use App\Order\Domain\Repository\PartnerOrderRepository;

readonly class CreatePartnerOrderHandler
{
    public function __construct(private PartnerOrderRepository $partnerOrderRepository) {}

    public function handle(CreatePartnerOrder $command): PartnerOrder
    {
        $existingOrder = $this->partnerOrderRepository->findByPartnerAndOrderId(
            $command->partnerId,
            $command->orderId,
        );

        if ($existingOrder) {
            throw new PartnerOrderAlreadyExistsException($command->partnerId, $command->orderId);
        }

        $order = new PartnerOrder(
            partnerId: $command->partnerId,
            orderId: $command->orderId,
            expectedDeliveryDate: $command->expectedDeliveryDate,
            totalValue: $command->totalValue,
            rawPayload: $command->rawPayload,
        );

        foreach ($command->items as $item) {
            $order->addItem(new PartnerOrderItem(
                productId: $item->productId,
                name: $item->name,
                price: $item->price,
                quantity: $item->quantity,
            ));
        }

        $order->ensureHasItems();
        $this->partnerOrderRepository->save($order);

        return $order;
    }
}
