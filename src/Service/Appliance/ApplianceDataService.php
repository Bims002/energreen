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

        $userAppliances = $lodgment ? $lodgment->getAppliances() : [];

        $userApplianceNames = array_map(
            fn($app) => $app->getName(),
            $userAppliances->toArray()
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
