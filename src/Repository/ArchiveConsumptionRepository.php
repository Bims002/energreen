<?php

namespace App\Repository;

use App\Entity\ArchiveConsumption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArchiveConsumption>
 */
class ArchiveConsumptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArchiveConsumption::class);
    }


    public function findLastArchives($user, int $limit = 12)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :val')
            ->setParameter('val', $user)
            ->orderBy('a.archived_at', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}