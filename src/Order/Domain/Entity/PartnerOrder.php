<?php

namespace App\Order\Domain\Entity;

use App\Order\Domain\Exception\InvalidPartnerOrderException;
use App\Order\Infrastructure\Doctrine\Repository\DoctrinePartnerOrderRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrinePartnerOrderRepository::class)]
#[ORM\Table(name: 'partner_order')]
#[ORM\UniqueConstraint(name: 'partner_order_unique', columns: ['partner_id', 'order_id'])]
class PartnerOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 255)]
    protected string $partnerId;

    #[ORM\Column(length: 255)]
    protected string $orderId;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    protected DateTimeImmutable $expectedDeliveryDate;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    protected string $totalValue;

    #[ORM\Column(type: Types::JSON)]
    protected array $rawPayload;

    /**
     * @var Collection<int, PartnerOrderItem>
     */
    #[ORM\OneToMany(targetEntity: PartnerOrderItem::class, mappedBy: 'partnerOrder', cascade: ['persist'], orphanRemoval: true)]
    protected Collection $orderItems;

    public function __construct(
        string $partnerId,
        string $orderId,
        DateTimeImmutable $expectedDeliveryDate,
        string $totalValue,
        array $rawPayload,
    ) {
        $this->assertNotBlank($partnerId, 'Partner ID cannot be empty.');
        $this->assertNotBlank($orderId, 'Order ID cannot be empty.');
        $this->assertMoneyFormat($totalValue, 'Total value must be a non-negative decimal number.');

        $this->partnerId = $partnerId;
        $this->orderId = $orderId;
        $this->expectedDeliveryDate = $expectedDeliveryDate;
        $this->totalValue = $totalValue;
        $this->rawPayload = $rawPayload;
        $this->orderItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPartnerId(): string
    {
        return $this->partnerId;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getExpectedDeliveryDate(): DateTimeImmutable
    {
        return $this->expectedDeliveryDate;
    }

    public function changeExpectedDeliveryDate(DateTimeImmutable $expectedDeliveryDate): void
    {
        $this->expectedDeliveryDate = $expectedDeliveryDate;
    }

    public function getTotalValue(): string
    {
        return $this->totalValue;
    }

    public function getRawPayload(): array
    {
        return $this->rawPayload;
    }

    /**
     * @return Collection<int, PartnerOrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addItem(PartnerOrderItem $orderItem): void
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->assignToOrder($this);
        }
    }

    public function ensureHasItems(): void
    {
        if ($this->orderItems->isEmpty()) {
            throw new InvalidPartnerOrderException('Order must contain at least one item.');
        }
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
