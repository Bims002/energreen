<?php

namespace App\Service\Carbon;

use App\Entity\BilanCarbone;

interface CarbonServiceInterface
{
    /**
     * Calcule la note carbone (A-F) basée sur le total d'émissions
     * 
     * @param float $total Total des émissions en kg CO2
     * @return array{label: string, color: string}
     */
    public function calculateCarbonGrade(float $total): array;

    /**
     * Génère des suggestions détaillées pour réduire l'empreinte carbone
     * 
     * @param BilanCarbone|null $bilan
     * @return array<string, string>
     */
    public function generateDetailedSuggestions(?BilanCarbone $bilan): array;
}
