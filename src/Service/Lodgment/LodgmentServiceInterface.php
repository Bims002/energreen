<?php

namespace App\Service\Lodgment;

use App\Entity\Lodgment;
use App\Entity\User;
use App\Entity\Appliance;
use App\Entity\Consumption;

interface LodgmentServiceInterface
{
    /**
     * Crée un nouveau logement pour un utilisateur
     * 
     * @param User $user
     * @param array<string, mixed> $data
     * @return Lodgment
     */
    public function createLodgment(User $user, array $data): Lodgment;

    /**
     * Crée une consommation initiale pour un utilisateur
     * 
     * @param User $user
     * @param array<string, mixed> $data
     * @return Consumption
     */
    public function createInitialConsumption(User $user, array $data): Consumption;

    /**
     * Toggle un appareil dans le logement
     * 
     * @param Lodgment $lodgment
     * @param Appliance $appliance
     * @return void
     */
    public function toggleAppliance(Lodgment $lodgment, Appliance $appliance): void;
}
