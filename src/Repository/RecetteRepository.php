<?php

namespace App\Repository;

use App\Entity\Recette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recette>
 */
class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recette::class);
    }

/**
 * @return Recette[] Returns an array of Recette objects
 */
   public function findByIngredient($nom): array
   {
       return $this->createQueryBuilder('r')
        ->InnerJoin('r.quantites', 'q')
        ->InnerJoin('q.ingredient', 'i')
           ->where('i.nom = :val')
           ->setParameter('val', $nom)
           ->getQuery()
           ->getResult()
       ;
   }

    public function foundByOrder($nbRecette = 4)
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.date', 'DESC')
            ->setMaxResults($nbRecette)
            ->getQuery()
            ->getResult()
        ;
    }

    public function foundByNote($nbRecette = 4)
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.commentaires', 'a')
            ->groupBy('r.id')
            ->orderBy('AVG(a.note)', 'DESC')
            ->setMaxResults($nbRecette)
            ->getQuery()
            ->getResult()
        ;
    }

    public function foundByUser($idUser)
    {
        return $this->createQueryBuilder('r')
            ->where('r.idUser = :val')
            ->setParameter(':val', $idUser)
            ->orderBy('r.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function getLastEtape($recette)
    {
    return $this->createQueryBuilder('r')
        ->leftJoin('r.etapes', 'e') 
        ->where('r.id = :recetteId')
        ->setParameter('recetteId', $recette->getId())
        ->orderBy('e.etapes', 'DESC') // Trier par numéro d'étape décroissant
        ->setMaxResults(1) // Récupérer seulement la dernière étape
        ->getQuery()
        ->getOneOrNullResult(); // Renvoie null si aucune étape
    }
}
