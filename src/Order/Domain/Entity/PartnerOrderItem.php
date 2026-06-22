<?php

namespace App\Order\Domain\Entity;

use App\Order\Domain\Exception\InvalidPartnerOrderException;
use App\Order\Infrastructure\Doctrine\Repository\PartnerOrderItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartnerOrderItemRepository::class)]
#[ORM\Table(name: 'partner_order_item')]
class PartnerOrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 255)]
    protected string $productId;

    #[ORM\Column(length: 255)]
    protected string $name;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    protected string $price;

    #[ORM\Column]
    protected int $quantity;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?PartnerOrder $partnerOrder = null;

    public function __construct(string $productId, string $name, string $price, int $quantity)
    {
        $this->assertNotBlank($productId, 'Product ID cannot be empty.');
        $this->assertNotBlank($name, 'Product name cannot be empty.');
        $this->assertMoneyFormat($price, 'Product price must be a non-negative decimal number.');

        if ($quantity <= 0) {
            throw new InvalidPartnerOrderException('Product quantity must be greater than zero.');
        }

        $this->productId = $productId;
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getPartnerOrder(): ?PartnerOrder
    {
        return $this->partnerOrder;
    }

    public function assignToOrder(PartnerOrder $partnerOrder): void
    {
        $this->partnerOrder = $partnerOrder;
    }

    private function assertNotBlank(string $value, string $message): void
    {
        if (trim($value) === '') {
            throw new InvalidPartnerOrderException($message);
        }
    }

    private function assertMoneyFormat(string $value, string $message): void
    {
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
            throw new InvalidPartnerOrderException($message);
        }
    }
}
