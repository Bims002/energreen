<?php

namespace App\Service;

class CarbonCalculator
{
    public function calculateAll(array $data): array
    {
        // 1. AXE LOGEMENT
        $surface = !empty($data['surface']) ? floatval($data['surface']) : 0;
        $typeLogementBase = !empty($data['type_logement']) ? floatval($data['type_logement']) : 0;
        $isolation = !empty($data['isolation_etat']) ? floatval($data['isolation_etat']) : 1;
        $energie = !empty($data['energie_principale']) ? floatval($data['energie_principale']) : 0;
        $logement = ($surface * ($typeLogementBase / 10) * $isolation * $energie)
            + (float) ($data['eau_chaude'] ?? 0)
            + (float) ($data['cuisson'] ?? 0)
            + ((float) ($data['clim_jours'] ?? 0) * 0.5)
            + (isset($data['piscine']) ? 800 : 0);

        // 2. AXE NUMÉRIQUE
        $numFabrication = ((float) ($data['qty_smartphone'] ?? 0) * 32) + ((float) ($data['qty_laptop'] ?? 0) * 156)
            + ((float) ($data['qty_desktop'] ?? 0) * 348) + ((float) ($data['qty_tablet'] ?? 0) * 63)
            + ((float) ($data['qty_tv'] ?? 0) * 200) + ((float) ($data['qty_gadget'] ?? 0) * 50);
        $numUsage = ((float) ($data['heures_streaming'] ?? 0) * 365 * (float) ($data['reseau_type'] ?? 0.004))
            + ((float) ($data['heures_gaming'] ?? 0) * 52 * 0.1)
            + (float) ($data['stockage_cloud'] ?? 0) + (float) ($data['box_internet'] ?? 0);
        $numerique = ($numFabrication * (float) ($data['num_duree_vie'] ?? 1)) + $numUsage;

        // 3. AXE ÉLECTROMÉNAGER
        $electroFab = ((float) ($data['qty_refri'] ?? 0) * 285) + ((float) ($data['qty_combiné'] ?? 0) * 345)
            + ((float) ($data['qty_congel'] ?? 0) * 310) + ((float) ($data['qty_lave_linge'] ?? 0) * 320)
            + ((float) ($data['qty_lave_vaisselle'] ?? 0) * 240) + ((float) ($data['qty_seche_linge'] ?? 0) * 300)
            + ((float) ($data['qty_four'] ?? 0) * 215) + ((float) ($data['qty_micro_ondes'] ?? 0) * 82)
            + ((float) ($data['qty_aspi'] ?? 0) * 52) + ((float) ($data['qty_cafe'] ?? 0) * 45);
        $electromenager = ($electroFab * (float) ($data['electro_duree_vie'] ?? 1)) + (isset($data['recharge_gaz']) ? 1200 : 0);

        // 4. AXE ALIMENTATION
        $baseAlim = (float) ($data['regime_alimentaire'] ?? 1550);
        $extrasProtéines = (float) ($data['frequence_viande_rouge'] ?? 0) + (float) ($data['frequence_viande_blanche'] ?? 0);
        $alimentation = ($baseAlim + $extrasProtéines + (isset($data['laitiers']) ? 182 : 0))
            * (float) ($data['coeff_saison'] ?? 1) * (float) ($data['coeff_bio'] ?? 1) * (float) ($data['coeff_avion'] ?? 1);

        // 5. AXE TRANSPORTS
        $voiture = ((float) ($data['km_voiture'] ?? 0) * (float) ($data['vehicule_moteur'] ?? 0) * (float) ($data['vehicule_taille'] ?? 1)) * (float) ($data['covoiturage'] ?? 1);
        $commun = ((float) ($data['km_train'] ?? 0) * 12 * 0.003) + ((float) ($data['trajets_bus'] ?? 0) * 52 * 0.15);
        $avion = ((float) ($data['vol_court'] ?? 0) * 260) + ((float) ($data['vol_moyen'] ?? 0) * 750) + ((float) ($data['vol_long'] ?? 0) * 1850);
        $transport = $voiture + $commun + $avion + ((float) ($data['km_voiture'] ?? 0) > 0 ? 0 : (float) ($data['mobilite_douce'] ?? 0));

        // 6. AXE TEXTILE
        $textileFab = ((float) ($data['qty_haut'] ?? 0) * 6.5) + ((float) ($data['qty_jean'] ?? 0) * 23.2)
            + ((float) ($data['qty_pull'] ?? 0) * 25.5) + ((float) ($data['qty_manteau'] ?? 0) * 62)
            + ((float) ($data['qty_chaussures'] ?? 0) * 17.5) + ((float) ($data['qty_accessoire'] ?? 0) * 1.8);
        $textile = ($textileFab * (float) ($data['habitudes_habillement'] ?? 1) * (float) ($data['matiere_textile'] ?? 1)) + (float) ($data['entretien_textile'] ?? 2);

        return [
            'Logement' => round($logement),
            'Numérique' => round($numerique),
            'Électroménager' => round($electromenager),
            'Alimentation' => round($alimentation),
            'Transports' => round($transport),
            'Textile' => round($textile)
        ];
    }
}