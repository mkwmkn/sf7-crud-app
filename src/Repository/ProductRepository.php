<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function getProductsByLimit(int $limit, int $currentCount): array {
        $firstResultIdx = $currentCount * $limit;
        return $this->createQueryBuilder('p')
            ->setFirstResult($firstResultIdx)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
