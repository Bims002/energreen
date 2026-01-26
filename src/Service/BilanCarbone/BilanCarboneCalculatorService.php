<?php

namespace App\Service\BilanCarbone;

use App\Entity\BilanCarbone;

class BilanCarboneCalculatorService implements BilanCarboneCalculatorServiceInterface
{
    public function calculateScores(array $data): array
    {
        // RÉCUPÉRATION DU NOMBRE D'HABITANTS
        $nbOccupants = (int) ($data['occupant'] ?? 1);
        if ($nbOccupants < 1)
            $nbOccupants = 1;

        // --- 1. LOGEMENT ---
        $consoBaseChauffage = (float) ($data['surface'] ?? 0) * 110;
        $scoreLogementTotal = $consoBaseChauffage * (float) ($data['isolation_etat'] ?? 1) * (float) ($data['energie_principale'] ?? 0);

        $scoreLogementTotal += (float) ($data['eau_chaude'] ?? 0);
        $scoreLogementTotal += (float) ($data['cuisson'] ?? 0);
        $scoreLogementTotal += (float) ($data['piscine'] ?? 0);
        $scoreLogementTotal += (float) ($data['clim_jours'] ?? 0) * 0.6;

        // DIVISION PAR OCCUPANT
        $scoreLogement = $scoreLogementTotal / $nbOccupants;

        // --- 2. NUMÉRIQUE (Version Amortie) ---
        $fabrication_num = (
            ((int) ($data['qty_smartphone'] ?? 0) * 80) +
            ((int) ($data['qty_laptop'] ?? 0) * 250) +
            ((int) ($data['qty_desktop'] ?? 0) * 400) +
            ((int) ($data['qty_tablet'] ?? 0) * 120) +
            ((int) ($data['qty_tv'] ?? 0) * 500) +
            ((int) ($data['qty_gadget'] ?? 0) * 50)
        ) * (float) ($data['num_duree_vie'] ?? 1);

        $usage_web = (float) ($data['heures_streaming'] ?? 0) * (float) ($data['reseau_type'] ?? 0.05) * 365;
        $usage_gaming = (float) ($data['heures_gaming'] ?? 0) * 0.1 * 52;
        $box_internet = (float) ($data['box_internet'] ?? 0) * 12;
        $cloud = (float) ($data['stockage_cloud'] ?? 0);

        $scoreNumerique = $fabrication_num + $usage_web + $usage_gaming + $box_internet + $cloud;

        // --- 3. ÉLECTROMÉNAGER (Version Amortie + Usage) ---
        $fabrication_electro = (
            ((int) ($data['qty_refri'] ?? 0) * 300) +
            ((int) ($data['qty_combiné'] ?? 0) * 450) +
            ((int) ($data['qty_congel'] ?? 0) * 350) +
            ((int) ($data['qty_lave_linge'] ?? 0) * 250) +
            ((int) ($data['qty_lave_vaisselle'] ?? 0) * 250) +
            ((int) ($data['qty_seche_linge'] ?? 0) * 300) +
            ((int) ($data['qty_four'] ?? 0) * 200) +
            ((int) ($data['qty_micro_ondes'] ?? 0) * 80) +
            ((int) ($data['qty_aspi'] ?? 0) * 60) +
            ((int) ($data['qty_cafe'] ?? 0) * 40)
        ) * (float) ($data['electro_duree_vie'] ?? 1);

        $conso_elec_moyenne = 150;
        $scoreElectroTotal = $fabrication_electro + $conso_elec_moyenne + (float) ($data['recharge_gaz'] ?? 0);

        // DIVISION PAR OCCUPANT
        $scoreElectro = $scoreElectroTotal / $nbOccupants;

        // --- 4. ALIMENTATION (Correction Erreur x52) ---
        $baseAlim = (float) ($data['regime_alimentaire'] ?? 2100);
        $scoreAlim = $baseAlim * (float) ($data['coeff_saison'] ?? 1) * (float) ($data['coeff_bio'] ?? 1) * (float) ($data['coeff_avion'] ?? 1);

        $scoreAlim += (float) ($data['frequence_viande_rouge'] ?? 0);
        $scoreAlim += (float) ($data['frequence_viande_blanche'] ?? 0);
        $scoreAlim += (float) ($data['laitiers'] ?? 0);

        // --- 5. TRANSPORTS (Cohérence Annuelle) ---
        $scoreTransports = (float) ($data['km_voiture'] ?? 0) * (float) ($data['vehicule_moteur'] ?? 0.2) * (float) ($data['vehicule_taille'] ?? 1) * (float) ($data['covoiturage'] ?? 1);

        $scoreTransports += ((float) ($data['km_train'] ?? 0) * 12 * 0.003);
        $scoreTransports += ((float) ($data['trajets_bus'] ?? 0) * 52 * 0.1);

        $scoreTransports += ((int) ($data['vol_court'] ?? 0) * 300);
        $scoreTransports += ((int) ($data['vol_moyen'] ?? 0) * 800);
        $scoreTransports += ((int) ($data['vol_long'] ?? 0) * 2000);
        $scoreTransports += (float) ($data['mobilite_douce'] ?? 0) * 2000;

        // --- 6. TEXTILE ---
        $scoreTextile = (
            ((int) ($data['qty_haut'] ?? 0) * 6) +
            ((int) ($data['qty_jean'] ?? 0) * 25) +
            ((int) ($data['qty_pull'] ?? 0) * 15) +
            ((int) ($data['qty_manteau'] ?? 0) * 50) +
            ((int) ($data['qty_chaussures'] ?? 0) * 15) +
            ((int) ($data['qty_accessoire'] ?? 0) * 2)
        );

        $scoreTextile = $scoreTextile * (float) ($data['habitudes_habillement'] ?? 1) * (float) ($data['matiere_textile'] ?? 1);
        $scoreTextile += (float) ($data['entretien_textile'] ?? 0);

        // --- TOTAL ---
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