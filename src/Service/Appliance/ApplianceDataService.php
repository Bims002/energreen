<?php

namespace App\Service\Appliance;

use App\Entity\Appliance;
use App\Entity\Lodgment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ApplianceDataService implements ApplianceDataServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function getApplianceData(User $user): array
    {
        $lodgment = $this->entityManager
            ->getRepository(Lodgment::class)
            ->findOneBy(['user' => $user], ['id' => 'DESC']);

        $userAppliances = [];
        if ($lodgment) {
            $appliances = $lodgment->getAppliances();
            $userAppliances = is_array($appliances) ? $appliances : $appliances->toArray();
        }
        $userApplianceNames = array_map(
            fn($app) => $app->getName(),
            $userAppliances
        );

        $allAppliances = $this->entityManager
            ->getRepository(Appliance::class)
            ->findAll();

        return [
            'lodgment' => $lodgment,
            'userAppliances' => $userAppliances,
            'userApplianceNames' => $userApplianceNames,
            'allAppliances' => $allAppliances
        ];
    }
}
