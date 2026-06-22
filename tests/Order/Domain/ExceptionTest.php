<?php

namespace App\Tests\Order\Domain;

use App\Order\Domain\Exception\PartnerOrderAlreadyExistsException;
use App\Order\Domain\Exception\PartnerOrderNotFoundException;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testNotFoundExceptionContainsOrderIdentity(): void
    {
        $exception = new PartnerOrderNotFoundException('nabytek-24', 'OBJ-20260612-8457');

        self::assertSame('nabytek-24', $exception->partnerId());
        self::assertSame('OBJ-20260612-8457', $exception->orderId());
        self::assertSame('Order "OBJ-20260612-8457" for partner "nabytek-24" was not found.', $exception->getMessage());
    }

    public function testAlreadyExistsExceptionContainsOrderIdentity(): void
    {
        $exception = new PartnerOrderAlreadyExistsException('nabytek-24', 'OBJ-20260612-8457');

        self::assertSame('nabytek-24', $exception->partnerId());
        self::assertSame('OBJ-20260612-8457', $exception->orderId());
        self::assertSame('Order "OBJ-20260612-8457" for partner "nabytek-24" already exists.', $exception->getMessage());
    }
}
