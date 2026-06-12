<?php

namespace App\Entity;

use App\Repository\PartnerOrderRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartnerOrderRepository::class)]
#[ORM\Table(name: 'partner_order')]
#[ORM\UniqueConstraint(name: 'partner_order_unique', columns: ['partner_id', 'order_id'])]
class PartnerOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 255)]
    protected ?string $partnerId = null;

    #[ORM\Column(length: 255)]
    protected ?string $orderId = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    protected ?DateTimeImmutable $expectedDeliveryDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    protected ?string $totalValue = null;

    #[ORM\Column(type: Types::JSON)]
    protected array $rawPayload = [];

    /**
     * @var Collection<int, PartnerOrderItem>
     */
    #[ORM\OneToMany(targetEntity: PartnerOrderItem::class, mappedBy: 'partnerOrder', cascade: ['persist'], orphanRemoval: true)]
    protected Collection $orderItems;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPartnerId(): ?string
    {
        return $this->partnerId;
    }

    public function setPartnerId(string $partnerId): static
    {
        $this->partnerId = $partnerId;

        return $this;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): static
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getExpectedDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->expectedDeliveryDate;
    }

    public function setExpectedDeliveryDate(\DateTimeImmutable $expectedDeliveryDate): static
    {
        $this->expectedDeliveryDate = $expectedDeliveryDate;

        return $this;
    }

    public function getTotalValue(): ?string
    {
        return $this->totalValue;
    }

    public function setTotalValue(string $totalValue): static
    {
        $this->totalValue = $totalValue;

        return $this;
    }

    public function getRawPayload(): array
    {
        return $this->rawPayload;
    }

    public function setRawPayload(array $rawPayload): static
    {
        $this->rawPayload = $rawPayload;

        return $this;
    }

    /**
     * @return Collection<int, PartnerOrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(PartnerOrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setPartnerOrder($this);
        }

        return $this;
    }

    public function removeOrderItem(PartnerOrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            if ($orderItem->getPartnerOrder() === $this) {
                $orderItem->setPartnerOrder(null);
            }
        }

        return $this;
    }
}
