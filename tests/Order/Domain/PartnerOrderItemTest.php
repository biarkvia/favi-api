<?php

namespace App\Tests\Order\Domain;

use App\Order\Domain\Entity\PartnerOrderItem;
use App\Order\Domain\Exception\InvalidPartnerOrderException;
use PHPUnit\Framework\TestCase;

class PartnerOrderItemTest extends TestCase
{
    public function testItemRequiresPositiveQuantity(): void
    {
        $this->expectException(InvalidPartnerOrderException::class);
        $this->expectExceptionMessage('Product quantity must be greater than zero.');

        new PartnerOrderItem('K-1187-DB', 'Dubova jidelni zidle', '1890.00', 0);
    }
}
