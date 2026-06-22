<?php

namespace App\Order\UI\Http;

use App\Order\Application\CreatePartnerOrder;
use App\Order\Application\CreatePartnerOrderHandler;
use App\Order\Application\CreatePartnerOrderItem;
use App\Order\Application\UpdateDeliveryDate;
use App\Order\Application\UpdateDeliveryDateHandler;
use App\Order\Domain\Exception\PartnerOrderAlreadyExistsException;
use App\Order\Domain\Exception\PartnerOrderNotFoundException;
use App\Order\UI\Http\DTO\OrderCreateDTO;
use App\Order\UI\Http\DTO\OrderItemDTO;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/orders', name: 'api_orders_')]
class OrderController extends AbstractController
{
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] OrderCreateDTO $dto, Request $request, CreatePartnerOrderHandler $handler): JsonResponse {
        try {
            $order = $handler->handle(new CreatePartnerOrder(
                partnerId: $dto->partnerId,
                orderId: $dto->orderId,
                expectedDeliveryDate: $dto->expectedDeliveryDate,
                totalValue: $dto->totalValue,
                items: array_map(
                    static fn (OrderItemDTO $product): CreatePartnerOrderItem => new CreatePartnerOrderItem(
                        productId: $product->productId,
                        name: $product->name,
                        price: $product->price,
                        quantity: $product->quantity,
                    ),
                    $dto->products,
                ),
                rawPayload: $request->toArray(),
            ));
        } catch (PartnerOrderAlreadyExistsException) {
            return $this->json(['error' => 'Order already exists'], Response::HTTP_CONFLICT);
        }

        return $this->json([
            'id' => $order->getId(),
            'partner_id' => $order->getPartnerId(),
            'order_id' => $order->getOrderId(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{partnerId}/{orderId}/delivery-date', name: 'update_delivery_date', methods: ['PATCH'])]
    public function updateDeliveryDate(string $partnerId, string $orderId, Request $request, UpdateDeliveryDateHandler $handler): JsonResponse {
        try {
            $payload = $request->toArray();
        } catch (JsonException) {
            return $this->json(['error' => 'Invalid JSON payload'], Response::HTTP_BAD_REQUEST);
        }

        $dateValue = $payload['expected_delivery_date'] ?? null;

        if (!is_string($dateValue) || $dateValue === '') {
            return $this->json(['error' => 'expected_delivery_date is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $expectedDeliveryDate = new DateTimeImmutable($dateValue);
        } catch (Exception) {
            return $this->json(['error' => 'expected_delivery_date must be a valid date'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $order = $handler->handle(new UpdateDeliveryDate($partnerId, $orderId, $expectedDeliveryDate));
        } catch (PartnerOrderNotFoundException) {
            return $this->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'partner_id' => $order->getPartnerId(),
            'order_id' => $order->getOrderId(),
            'expected_delivery_date' => $order->getExpectedDeliveryDate()->format('Y-m-d'),
        ]);
    }
}
