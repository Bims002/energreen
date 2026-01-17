<?php

namespace App\Service;

use App\Entity\Consumption;
use App\Entity\ArchiveConsumption;
use Doctrine\ORM\EntityManagerInterface;

class ConsumptionManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function archiveAndDeleteOldConsumption(Consumption $oldConsumption): void
    {
        $archive = new ArchiveConsumption();
        $archive->setUser($oldConsumption->getUser());
        $archive->setTotalKwh($oldConsumption->getTotalKwh() ?? 0);
        $archive->setEstimatedPrice($oldConsumption->getEstimatedPrice() ?? 0);

        $this->em->persist($archive);
        $this->em->remove($oldConsumption);
        // On ne fait pas de flush ici, on le fera dans le contrôleur
    }
}