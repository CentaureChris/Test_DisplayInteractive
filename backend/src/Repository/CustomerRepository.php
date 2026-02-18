<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

   /**
    * @return Customer[] Returns an array of Customer objects
    */
   public function findAll(): array
   {
       return $this->createQueryBuilder('c')
           ->orderBy('c.id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
   }
}
