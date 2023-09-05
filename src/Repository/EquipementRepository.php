<?php

namespace App\Repository;

use App\Entity\Equipement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicule>
 *
 * @method Equipement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Equipement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Equipement[]    findAll()
 * @method Equipement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipement::class);
    }

    public function save(Equipement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Equipement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findBySearchQuery($query)
    {
        $qb = $this->createQueryBuilder('e');

        if ($query) {
            $qb->andWhere('e.nom LIKE :query')
                ->setParameter('query', '%'.$query.'%');
        }

        return $qb
            ->orderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult();
            
    }
    // Add your custom query methods here
    public function chartRepository()
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id) as count, IDENTITY(e.employe) as employee_id')
            ->groupBy('e.employe')
            ->getQuery()
            ->getResult();
    }
    public function countByState()
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.etat, COUNT(e.id) as count')
            ->groupBy('e.etat');

        return $qb->getQuery()->getResult();
    }
    
//    /**
//     * @return Equipement[] Returns an array of Equipement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Equipement
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
public function findAllEquipements()
    {
        return $this->createQueryBuilder('e')
            ->getQuery()
            ->getResult();
    }
    


}