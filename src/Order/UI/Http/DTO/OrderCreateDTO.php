<?php

namespace App\Order\UI\Http\DTO;

use DateTimeImmutable;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

readonly class OrderCreateDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[SerializedName('partner_id')]
        public string $partnerId,

        #[Assert\NotBlank]
        #[SerializedName('order_id')]
        public string $orderId,

        #[Assert\NotBlank]
        #[Assert\Type("\DateTimeInterface")]
        #[SerializedName('expected_delivery_date')]
        public DateTimeImmutable $expectedDeliveryDate,

        #[Assert\NotBlank]
        #[Assert\Regex('/^\d+(\.\d{1,2})?$/')]
        #[SerializedName('total_value')]
        public string $totalValue,

        /** @var OrderItemDTO[] */
        #[Assert\NotBlank]
        #[Assert\Count(min: 1, minMessage: 'At least one product in the order')]
        #[Assert\All([new Assert\Type(OrderItemDTO::class)])]
        #[Assert\Valid]
        public array $products,
    ) {}
}
