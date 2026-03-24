<?php

namespace App\Service\BilanCarbone;

use App\Entity\BilanCarbone;

interface BilanCarboneCalculatorServiceInterface
{
    /**
     * Calcule tous les scores du bilan carbone
     * 
     * @param array<string, mixed> $data
     * @return array{logement: float, numerique: float, electromenager: float, alimentation: float, transports: float, textile: float, total: float}
     */
    public function calculateScores(array $data): array;

    /**
     * Crée un BilanCarbone à partir des scores calculés
     * 
     * @param array{logement: float, numerique: float, electromenager: float, alimentation: float, transports: float, textile: float, total: float} $scores
     * @return BilanCarbone
     */
    public function createBilanFromScores(array $scores): BilanCarbone;
}
