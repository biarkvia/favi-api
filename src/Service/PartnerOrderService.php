<?php

namespace App\Service;

use App\DTO\OrderCreateDTO;
use App\Entity\PartnerOrder;
use App\Entity\PartnerOrderItem;
use App\Exception\PartnerOrderAlreadyExistsException;
use App\Exception\PartnerOrderNotFoundException;
use App\Repository\PartnerOrderRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

readonly class PartnerOrderService
{
    public function __construct(private PartnerOrderRepository $partnerOrderRepository, private EntityManagerInterface $entityManager) {}

    public function create(OrderCreateDTO $dto, array $rawPayload): PartnerOrder
    {
        $existingOrder = $this->partnerOrderRepository->findOneBy([
            'partnerId' => $dto->partnerId,
            'orderId' => $dto->orderId,
        ]);

        if ($existingOrder) {
            throw new PartnerOrderAlreadyExistsException();
        }

        $order = (new PartnerOrder())
            ->setPartnerId($dto->partnerId)
            ->setOrderId($dto->orderId)
            ->setExpectedDeliveryDate($dto->expectedDeliveryDate)
            ->setTotalValue($dto->totalValue)
            ->setRawPayload($rawPayload);

        foreach ($dto->products as $productDto) {
            $orderItem = (new PartnerOrderItem())
                ->setProductId($productDto->productId)
                ->setName($productDto->name)
                ->setPrice($productDto->price)
                ->setQuantity($productDto->quantity);

            $order->addOrderItem($orderItem);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }

    public function updateExpectedDeliveryDate(string $partnerId, string $orderId, DateTimeImmutable $expectedDeliveryDate): PartnerOrder
    {
        $order = $this->partnerOrderRepository->findOneBy([
            'partnerId' => $partnerId,
            'orderId' => $orderId,
        ]);

        if (!$order) {
            throw new PartnerOrderNotFoundException();
        }

        $order->setExpectedDeliveryDate($expectedDeliveryDate);
        $this->entityManager->flush();

        return $order;
    }
}
