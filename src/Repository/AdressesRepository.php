<?php

namespace App\Repository;

use App\Entity\Adresse;
use App\Entity\Adresses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Adresses|null find($id, $lockMode = null, $lockVersion = null)
 * @method Adresses|null findOneBy(array $criteria, array $orderBy = null)
 * @method Adresses[]    findAll()
 * @method Adresses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdressesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adresses::class);
    }

    /**
     * @return Adresse[] Returns an array of Adresse objects
     */

    public function findAdressesByVille($ville)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.ville = :val')
            ->setParameter('val', $ville)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
    /**
     * @return Adresse[] Returns an array of Adresse objects
     */

    public function findAdressesByVilleAndRue($ville, $rue)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.ville = :val1')
            ->setParameter('val1', $ville)
            ->andWhere('a.nomRue = :val2')
            ->setParameter('val2', $rue)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }



    /*
    public function findOneBySomeField($value): ?Adresse
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
