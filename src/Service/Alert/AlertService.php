<?php

namespace App\Service\Alert;

use App\Entity\Consumption;

class AlertService implements AlertServiceInterface
{
    public function generateSmartAlerts(?Consumption $cons): array
    {
        $alerts = [];

        if (!$cons) {
            return [];
        }

        $totalKwh = $cons->getTotalKwh();
        $estimatedPrice = $cons->getEstimatedPrice();

        // ALERTE 2 : GROS CONSOMMATEUR (Simulation basée sur le total)
        // Si la consommation dépasse un certain seuil, on suppose qu'un gros appareil est en cause.
        if ($totalKwh > 300) {
            // Calcul théorique : part du plus gros consommateur (~30%)
            $partGrosConso = round($estimatedPrice * 0.30, 2);

            $alerts[] = [
                'type' => 'warning', // warning, danger, info
                'title' => 'Gros Consommateur Identifié',
                'icon' => 'fas fa-plug',
                'message' => "Votre <strong>Chauffage / Sèche-linge</strong> semble être votre premier poste de dépense (~{$partGrosConso} €/mois). Essayez de réduire son utilisation ou vérifiez son isolation.",
                'is_critical' => true
            ];
        }
        // Si consommation très élevée
        elseif ($totalKwh > 600) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Surconsommation Critique',
                'icon' => 'fas fa-exclamation-triangle',
                'message' => "Votre consommation est anormalement élevée. Vérifiez si vous n'avez pas un appareil défectueux (ballon d'eau chaude, vieux frigo).",
                'is_critical' => true
            ];
        } else {
            // Message rassurant si tout va bien
            $alerts[] = [
                'type' => 'success',
                'title' => 'Consommation Maîtrisée',
                'icon' => 'fas fa-check-circle',
                'message' => "Bravo ! Aucun poste de dépense excessif détecté cette période.",
                'is_critical' => false
            ];
        }

        return $alerts;
    }
}
