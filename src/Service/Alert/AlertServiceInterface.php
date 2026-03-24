<?php

namespace App\Service\Alert;

use App\Entity\Consumption;

interface AlertServiceInterface
{
    /**
     * Génère des alertes intelligentes basées sur la consommation
     * 
     * @param Consumption|null $consumption
     * @return array<int, array{type: string, title: string, icon: string, message: string, is_critical: bool}>
     */
    public function generateSmartAlerts(?Consumption $consumption): array;
}
