<?php

namespace App\Order\Domain\Exception;

use RuntimeException;

final class PartnerOrderAlreadyExistsException extends RuntimeException
{
    public function __construct(
        private readonly string $partnerId,
        private readonly string $orderId,
    ) {
        parent::__construct(sprintf(
            'Order "%s" for partner "%s" already exists.',
            $orderId,
            $partnerId,
        ));
    }

    public function partnerId(): string
    {
        return $this->partnerId;
    }

    public function orderId(): string
    {
        return $this->orderId;
    }
}
