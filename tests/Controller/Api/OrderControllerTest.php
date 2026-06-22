<?php

namespace App\Tests\Controller\Api;

use App\Order\Domain\Entity\PartnerOrder;
use App\Order\Domain\Entity\PartnerOrderItem;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        self::ensureKernelShutdown();

        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->truncateTables();
    }

    public function testCreateOrderStoresValidOrder(): void
    {
        $payload = $this->validOrderPayload();

        $this->requestJson('POST', '/api/orders', $payload);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = $this->decodeResponse();
        self::assertArrayNotHasKey('status', $responseData);
        self::assertIsInt($responseData['id']);
        self::assertSame('nabytek-24', $responseData['partner_id']);
        self::assertSame('OBJ-20260612-8457', $responseData['order_id']);

        $this->entityManager->clear();
        $order = $this->findOrder('nabytek-24', 'OBJ-20260612-8457');

        self::assertNotNull($order);
        self::assertSame('nabytek-24', $order->getPartnerId());
        self::assertSame('OBJ-20260612-8457', $order->getOrderId());
        self::assertSame('2026-06-24', $order->getExpectedDeliveryDate()?->format('Y-m-d'));
        self::assertSame('8450.00', $order->getTotalValue());
        self::assertSame($payload, $order->getRawPayload());
        self::assertCount(2, $order->getOrderItems());

        $firstItem = $order->getOrderItems()->first();

        self::assertInstanceOf(PartnerOrderItem::class, $firstItem);
        self::assertSame('K-1187-DB', $firstItem->getProductId());
        self::assertSame('Dubova jidelni zidle', $firstItem->getName());
        self::assertSame('1890.00', $firstItem->getPrice());
        self::assertSame(4, $firstItem->getQuantity());
    }

    public function testCreateOrderRequiresAtLeastOneProduct(): void
    {
        $payload = $this->validOrderPayload(['products' => []]);

        $this->requestJson('POST', '/api/orders', $payload);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(0, $this->countOrders());
    }

    public function testCreateOrderRejectsDuplicatePartnerOrderId(): void
    {
        $payload = $this->validOrderPayload();

        $this->requestJson('POST', '/api/orders', $payload);
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->requestJson('POST', '/api/orders', $payload);

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        self::assertSame(1, $this->countOrders());
    }

    public function testUpdateExpectedDeliveryDate(): void
    {
        $this->requestJson('POST', '/api/orders', $this->validOrderPayload());
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->requestJson('PATCH', '/api/orders/nabytek-24/OBJ-20260612-8457/delivery-date', [
            'expected_delivery_date' => '2026-06-28',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = $this->decodeResponse();
        self::assertArrayNotHasKey('status', $responseData);
        self::assertSame('nabytek-24', $responseData['partner_id']);
        self::assertSame('OBJ-20260612-8457', $responseData['order_id']);
        self::assertSame('2026-06-28', $responseData['expected_delivery_date']);

        $this->entityManager->clear();
        $order = $this->findOrder('nabytek-24', 'OBJ-20260612-8457');

        self::assertNotNull($order);
        self::assertSame('2026-06-28', $order->getExpectedDeliveryDate()?->format('Y-m-d'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->entityManager);
    }

    private function requestJson(string $method, string $uri, array $payload): void
    {
        $content = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->client->request($method, $uri, [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],

            $content,
        );
    }

    private function validOrderPayload(array $overrides = []): array
    {
        $payload = [
            'partner_id' => 'nabytek-24',
            'order_id' => 'OBJ-20260612-8457',
            'expected_delivery_date' => '2026-06-24',
            'total_value' => '8450.00',
            'products' => [
                [
                    'product_id' => 'K-1187-DB',
                    'name' => 'Dubova jidelni zidle',
                    'price' => '1890.00',
                    'quantity' => 4,
                ],
                [
                    'product_id' => 'L-2043-BK',
                    'name' => 'Stolni lampa cerna',
                    'price' => '890.00',
                    'quantity' => 1,
                ],
            ],
        ];

        foreach ($overrides as $key => $value) {
            $payload[$key] = $value;
        }

        return $payload;
    }

    private function decodeResponse(): array
    {
        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        return $data;
    }

    private function findOrder(string $partnerId, string $orderId): ?PartnerOrder
    {
        return $this->entityManager->getRepository(PartnerOrder::class)->findOneBy([
            'partnerId' => $partnerId,
            'orderId' => $orderId,
        ]);
    }

    private function countOrders(): int
    {
        return $this->entityManager->getRepository(PartnerOrder::class)->count([]);
    }

    /**
     * @throws Exception
     */
    private function truncateTables(): void
    {
        $connection = $this->entityManager->getConnection();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        try {
            $connection->executeStatement('TRUNCATE TABLE partner_order_item');
            $connection->executeStatement('TRUNCATE TABLE partner_order');
        } finally {
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
