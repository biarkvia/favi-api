<?php

namespace App\Controller\Api;

use App\DTO\OrderCreateDTO;
use App\Exception\PartnerOrderAlreadyExistsException;
use App\Exception\PartnerOrderNotFoundException;
use App\Service\PartnerOrderService;
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
    public function create(#[MapRequestPayload] OrderCreateDTO $dto, Request $request, PartnerOrderService $partnerOrderService): JsonResponse {
        try {
            $order = $partnerOrderService->create($dto, $request->toArray());
        } catch (PartnerOrderAlreadyExistsException) {
            return $this->json(['error' => 'Order already exists'], Response::HTTP_CONFLICT);
        }

        return $this->json(['status' => 'success', 'id' => $order->getId()], Response::HTTP_CREATED);
    }

    #[Route('/{partnerId}/{orderId}/delivery-date', name: 'update_delivery_date', methods: ['PATCH'])]
    public function updateDeliveryDate(string $partnerId, string $orderId, Request $request, PartnerOrderService $partnerOrderService): JsonResponse {
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
            $order = $partnerOrderService->updateExpectedDeliveryDate($partnerId, $orderId, $expectedDeliveryDate);
        } catch (PartnerOrderNotFoundException) {
            return $this->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'status' => 'success',
            'partner_id' => $order->getPartnerId(),
            'order_id' => $order->getOrderId(),
            'expected_delivery_date' => $order->getExpectedDeliveryDate()?->format('Y-m-d'),
        ]);
    }
}
