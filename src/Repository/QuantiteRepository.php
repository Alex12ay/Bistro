<?php

namespace App\Repository;

use App\Entity\Quantite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quantite>
 */
class QuantiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quantite::class);
    }

        /**
         * @return Quantite[] Returns an array of Quantite objects
         */
        public function findByRecette($value): array
        {
            return $this->createQueryBuilder('q')
                ->andWhere('q.recette = :val')
                ->setParameter('val', $value)
                ->getQuery()
                ->getResult()
            ;
        }

    //    public function findIngredient($id): ?Quantite
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->where('q.ingredient = :val')
    //            ->where('q.recette = r')
    //            ->setParameter('val', $id)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
