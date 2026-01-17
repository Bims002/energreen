<?php

namespace App\Repository;

use App\Entity\ArchiveBilanCarbone; // Vérifiez bien le nom ici
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArchiveBilanCarbone>
 */
class ArchiveBilanCarboneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        // On passe la classe de l'entité au parent
        parent::__construct($registry, ArchiveBilanCarbone::class);
    }
}