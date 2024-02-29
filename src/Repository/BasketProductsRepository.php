<?php

namespace App\Repository;

use App\Entity\BasketProducts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BasketProducts>
 *
 * @method BasketProducts|null find($id, $lockMode = null, $lockVersion = null)
 * @method BasketProducts|null findOneBy(array $criteria, array $orderBy = null)
 * @method BasketProducts[]    findAll()
 * @method BasketProducts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BasketProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BasketProducts::class);
    }

    //    /**
    //     * @return BasketProducts[] Returns an array of BasketProducts objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?BasketProducts
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
