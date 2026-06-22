<?php

namespace App\Order\Infrastructure\Doctrine\Repository;

use App\Order\Domain\Entity\PartnerOrderItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PartnerOrderItem>
 */
class PartnerOrderItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PartnerOrderItem::class);
    }
}
