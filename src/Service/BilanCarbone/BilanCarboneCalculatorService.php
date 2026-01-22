<?php

namespace App\Service\BilanCarbone;

use App\Entity\BilanCarbone;

class BilanCarboneCalculatorService implements BilanCarboneCalculatorServiceInterface
{
    public function calculateScores(array $data): array
    {
        // Calcul du score Logement
        $scoreLogement = ((float) ($data['surface'] ?? 0) * (float) ($data['isolation_etat'] ?? 1) * (float) ($data['energie_principale'] ?? 0));
        $scoreLogement += (float) ($data['eau_chaude'] ?? 0) + (float) ($data['cuisson'] ?? 0) + (float) ($data['piscine'] ?? 0);

        // Calcul du score Numérique
        $scoreNumerique = ((int) ($data['qty_smartphone'] ?? 0) * 80) + ((int) ($data['qty_laptop'] ?? 0) * 250);
        $scoreNumerique += ((float) ($data['heures_streaming'] ?? 0) * (float) ($data['reseau_type'] ?? 0) * 365);

        // Calcul du score Électroménager
        $scoreElectro = ((int) ($data['qty_refri'] ?? 0) * 300) + ((int) ($data['qty_lave_linge'] ?? 0) * 200);

        // Calcul du score Alimentation
        $scoreAlim = (float) ($data['regime_alimentaire'] ?? 2100) * (float) ($data['coeff_saison'] ?? 1);

        // Calcul du score Transports
        $scoreTransports = ((float) ($data['km_voiture'] ?? 0) * (float) ($data['vehicule_moteur'] ?? 0));
        $scoreTransports += ((int) ($data['vol_long'] ?? 0) * 1500);

        // Calcul du score Textile
        $scoreTextile = ((int) ($data['qty_jean'] ?? 0) * 25) + ((int) ($data['qty_chaussures'] ?? 0) * 15);

        // Calcul du total
        $total = $scoreLogement + $scoreNumerique + $scoreElectro + $scoreAlim + $scoreTransports + $scoreTextile;

        return [
            'logement' => round($scoreLogement, 2),
            'numerique' => round($scoreNumerique, 2),
            'electromenager' => round($scoreElectro, 2),
            'alimentation' => round($scoreAlim, 2),
            'transports' => round($scoreTransports, 2),
            'textile' => round($scoreTextile, 2),
            'total' => round($total, 2),
        ];
    }

    public function createBilanFromScores(array $scores): BilanCarbone
    {
        $bilan = new BilanCarbone();
        $bilan->setLogement($scores['logement']);
        $bilan->setNumerique($scores['numerique']);
        $bilan->setElectromenager($scores['electromenager']);
        $bilan->setAlimentation($scores['alimentation']);
        $bilan->setTransports($scores['transports']);
        $bilan->setTextile($scores['textile']);
        $bilan->setTotal($scores['total']);

        return $bilan;
    }
}
