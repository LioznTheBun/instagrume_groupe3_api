<?php

namespace App\Repository;

use App\Entity\RatingCommentaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RatingCommentaire>
 *
 * @method RatingCommentaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method RatingCommentaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method RatingCommentaire[]    findAll()
 * @method RatingCommentaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RatingCommentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RatingCommentaire::class);
    }

//    /**
//     * @return RatingCommentaire[] Returns an array of RatingCommentaire objects
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

//    public function findOneBySomeField($value): ?RatingCommentaire
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
