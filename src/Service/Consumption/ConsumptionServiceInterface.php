<?php

namespace App\Service\Consumption;

use App\Entity\Consumption;
use App\Entity\User;

interface ConsumptionServiceInterface
{
    /**
     * Génère des suggestions pour réduire la consommation électrique
     * 
     * @param Consumption|null $consumption
     * @return array<string, string>
     */
    public function generateElectricSuggestions(?Consumption $consumption): array;

    /**
     * Sauvegarde une nouvelle consommation et archive l'ancienne
     * 
     * @param User $user
     * @param float $totalKwh
     * @param float $totalPrice
     * @return Consumption
     */
    public function saveConsumption(User $user, float $totalKwh, float $totalPrice): Consumption;
}
