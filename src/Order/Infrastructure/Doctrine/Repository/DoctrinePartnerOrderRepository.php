<?php

namespace App\Order\Infrastructure\Doctrine\Repository;

use App\Order\Domain\Entity\PartnerOrder;
use App\Order\Domain\Repository\PartnerOrderRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PartnerOrder>
 */
class DoctrinePartnerOrderRepository extends ServiceEntityRepository implements PartnerOrderRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($registry, PartnerOrder::class);
    }

    public function save(PartnerOrder $order): void
    {
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }

    public function findByPartnerAndOrderId(string $partnerId, string $orderId): ?PartnerOrder
    {
        return $this->findOneBy([
            'partnerId' => $partnerId,
            'orderId' => $orderId,
        ]);
    }
}
