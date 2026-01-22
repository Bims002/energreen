<?php

namespace App\Service\Carbon;

use App\Entity\BilanCarbone;

class CarbonService implements CarbonServiceInterface
{
    public function calculateCarbonGrade(float $total): array
    {
        if ($total < 2000) {
            return ['label' => 'A', 'color' => '#00C853'];
        } elseif ($total < 4000) {
            return ['label' => 'B', 'color' => '#64DD17'];
        } elseif ($total < 6000) {
            return ['label' => 'C', 'color' => '#FFD600'];
        } elseif ($total < 8000) {
            return ['label' => 'D', 'color' => '#FF6F00'];
        } elseif ($total < 10000) {
            return ['label' => 'E', 'color' => '#DD2C00'];
        } else {
            return ['label' => 'F', 'color' => '#B71C1C'];
        }
    }

    public function generateDetailedSuggestions(?BilanCarbone $bilan): array
    {
        if (!$bilan) {
            return [];
        }

        $suggestions = [];
        $categories = [
            'Transport' => $bilan->getTransports(),
            'Alimentation' => $bilan->getAlimentation(),
            'Logement' => $bilan->getLogement(),
            'Numérique' => $bilan->getNumerique(),
            'Électroménager' => $bilan->getElectromenager(),
            'Textile' => $bilan->getTextile(),
        ];

        arsort($categories);
        $topCategories = array_slice($categories, 0, 3, true);

        $allSuggestions = [
            'Transport' => [
                'Privilégiez les transports en commun ou le covoiturage',
                'Optez pour un véhicule électrique ou hybride',
                'Utilisez le vélo pour les trajets courts',
            ],
            'Alimentation' => [
                'Réduisez votre consommation de viande',
                'Achetez des produits locaux et de saison',
                'Limitez le gaspillage alimentaire',
            ],
            'Logement' => [
                'Améliorez l\'isolation de votre logement',
                'Baissez le chauffage d\'1°C',
                'Installez un thermostat programmable',
            ],
            'Numérique' => [
                'Limitez le streaming vidéo en haute définition',
                'Éteignez vos appareils au lieu de les laisser en veille',
                'Gardez vos appareils plus longtemps',
            ],
            'Électroménager' => [
                'Choisissez des appareils de classe A+++',
                'Utilisez le lave-linge à basse température',
                'Dégivrez régulièrement votre congélateur',
            ],
            'Textile' => [
                'Achetez des vêtements de seconde main',
                'Réparez au lieu de jeter',
                'Privilégiez les matières durables',
            ],
        ];

        $index = (int) (time() / 86400) % 3;

        foreach ($topCategories as $category => $value) {
            if ($value > 0 && isset($allSuggestions[$category])) {
                $suggestions[$category] = $allSuggestions[$category][$index];
            }
        }

        return $suggestions;
    }
}
