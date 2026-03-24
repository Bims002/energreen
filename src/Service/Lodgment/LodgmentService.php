<?php

namespace App\Service\Lodgment;

use App\Entity\Appliance;
use App\Entity\Consumption;
use App\Entity\Lodgment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class LodgmentService implements LodgmentServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function createLodgment(User $user, array $data): Lodgment
    {
        $lodgment = new Lodgment();
        $lodgment->setLodgmentType($data['lodgment_type'] ?? '');
        $lodgment->setSurface((int) ($data['surface'] ?? 0));
        $lodgment->setOccupant((int) ($data['occupants'] ?? 0));
        $lodgment->setUser($user);

        // Ajout des appareils
        if (isset($data['appliances']) && is_array($data['appliances'])) {
            foreach ($data['appliances'] as $applianceId) {
                $appliance = $this->entityManager
                    ->getRepository(Appliance::class)
                    ->find($applianceId);

                if ($appliance) {
                    $lodgment->addAppliance($appliance);
                }
            }
        }

        $this->entityManager->persist($lodgment);
        return $lodgment;
    }

    public function createInitialConsumption(User $user, array $data): Consumption
    {
        $consumption = new Consumption();
        $consumption->setPastConsumption((float) ($data['past_consumption'] ?? 0));
        $consumption->setBillingDate(new \DateTime($data['billing_date'] ?? 'now'));
        $consumption->setUser($user);

        $this->entityManager->persist($consumption);
        return $consumption;
    }

    public function toggleAppliance(Lodgment $lodgment, Appliance $appliance): void
    {
        if ($lodgment->getAppliances()->contains($appliance)) {
            $lodgment->removeAppliance($appliance);
        } else {
            $lodgment->addAppliance($appliance);
        }
    }
}
