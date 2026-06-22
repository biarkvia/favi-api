<?php

namespace App\Tests\Order\Domain;

use App\Order\Domain\Entity\PartnerOrder;
use App\Order\Domain\Entity\PartnerOrderItem;
use App\Order\Domain\Exception\InvalidPartnerOrderException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class PartnerOrderTest extends TestCase
{
    public function testCreatesOrderWithRequiredData(): void
    {
        $order = $this->createOrder();
        $item = new PartnerOrderItem('K-1187-DB', 'Dubova jidelni zidle', '1890.00', 4);

        $order->addItem($item);
        $order->ensureHasItems();

        self::assertSame('nabytek-24', $order->getPartnerId());
        self::assertSame('OBJ-20260612-8457', $order->getOrderId());
        self::assertSame('2026-06-24', $order->getExpectedDeliveryDate()->format('Y-m-d'));
        self::assertSame('8450.00', $order->getTotalValue());
        self::assertSame(['source' => 'api'], $order->getRawPayload());
        self::assertCount(1, $order->getOrderItems());
        self::assertSame($order, $item->getPartnerOrder());
    }

    public function testCannotCreateOrderWithoutItems(): void
    {
        $order = $this->createOrder();

        $this->expectException(InvalidPartnerOrderException::class);
        $this->expectExceptionMessage('Order must contain at least one item.');

        $order->ensureHasItems();
    }

    public function testCanChangeDeliveryDate(): void
    {
        $order = $this->createOrder();

        $order->changeExpectedDeliveryDate(new DateTimeImmutable('2026-06-28'));

        self::assertSame('2026-06-28', $order->getExpectedDeliveryDate()->format('Y-m-d'));
    }

    private function createOrder(): PartnerOrder
    {
        return new PartnerOrder(
            partnerId: 'nabytek-24',
            orderId: 'OBJ-20260612-8457',
            expectedDeliveryDate: new DateTimeImmutable('2026-06-24'),
            totalValue: '8450.00',
            rawPayload: ['source' => 'api'],
        );
    }
}
