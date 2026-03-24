<?php

namespace App\Service\Dashboard;

use App\Entity\Consumption;
use App\Entity\User;
use App\Repository\ArchiveConsumptionRepository;

interface DashboardDataServiceInterface
{
    /**
     * Vérifie si un rappel de mise à jour est nécessaire
     * 
     * @param Consumption|null $latestConsumption
     * @return bool
     */
    public function shouldShowUpdateReminder(?Consumption $latestConsumption): bool;

    /**
     * Prépare les données pour le graphique mensuel
     * 
     * @param User $user
     * @return array{labels: array<string>, data: array<float>}
     */
    public function prepareMonthlyChartData(User $user): array;

    /**
     * Calcule les émissions CO2 basées sur la consommation en kWh
     * 
     * @param float $kwh
     * @return float
     */
    public function calculateCO2Emissions(float $kwh): float;
}
