<?php

namespace App\Repository;

use App\Entity\Purchase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Purchase>
 */
class PurchaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchase::class);
    }

  /**
     * @return list<Purchase>
     */
    public function findByCustomerId(int $customerId): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.customer', 'customer')
            ->andWhere('customer.id = :customerId')
            ->setParameter('customerId', $customerId)
            ->orderBy('p.date', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByIdentifierId(string $identifierId): ?Purchase
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.purchaseIdentifier = :identifierId')
            ->setParameter('identifierId', $identifierId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
