<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

readonly class OrderItemDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[SerializedName('product_id')]
        public string $productId,

        #[Assert\NotBlank]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Regex('/^\d+(\.\d{1,2})?$/')]
        public string $price,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $quantity) {}
}
