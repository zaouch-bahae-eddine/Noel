<?php

namespace App\Repository;

use App\Entity\Personnes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Personnes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Personnes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Personnes[]    findAll()
 * @method Personnes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonnesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personnes::class);
    }

    // /**
    //  * @return Personnes[] Returns an array of Personnes objects
    //  */

    public function findPersoneFiltreAge($min, $max)
    {
        $min = new \DateTime($this->age($min));
        $max = new \DateTime($this->age($max));
        return $this->createQueryBuilder('p')
            ->andWhere('p.naissance >= :max')
            ->setParameter('max', $max->format('Y-m-d H:i:s'))
            ->andWhere('p.naissance <= :min')
            ->setParameter('min', $min->format('Y-m-d H:i:s'))
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;

    }
    private function age($age){
        return date('Y', strtotime($age . ' years ago')).'-01-01';
    }
    /*
    public function findOneBySomeField($value): ?Personnes
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
