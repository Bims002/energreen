<?php

namespace App\Service\Appliance;

use App\Entity\Lodgment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

interface ApplianceDataServiceInterface
{
    /**
     * Récupère les données d'appareils pour un utilisateur
     * 
     * @param User $user
     * @return array{lodgment: Lodgment|null, userAppliances: array, userApplianceNames: array<string>, allAppliances: array}
     */
    public function getApplianceData(User $user): array;
}
