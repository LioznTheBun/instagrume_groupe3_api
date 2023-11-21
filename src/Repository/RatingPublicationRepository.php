<?php

namespace App\Repository;

use App\Entity\RatingPublication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RatingPublication>
 *
 * @method RatingPublication|null find($id, $lockMode = null, $lockVersion = null)
 * @method RatingPublication|null findOneBy(array $criteria, array $orderBy = null)
 * @method RatingPublication[]    findAll()
 * @method RatingPublication[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RatingPublicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RatingPublication::class);
    }

//    /**
//     * @return RatingPublication[] Returns an array of RatingPublication objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RatingPublication
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
