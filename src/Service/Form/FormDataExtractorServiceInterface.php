<?php

namespace App\Service\Form;

use Symfony\Component\HttpFoundation\Request;

interface FormDataExtractorServiceInterface
{
    /**
     * Extrait les données d'un formulaire de profil
     * 
     * @param Request $request
     * @return array<string, mixed>
     */
    public function extractProfileData(Request $request): array;

    /**
     * Extrait et valide les données de consommation
     * 
     * @param Request $request
     * @return array{totalKwh: float, totalPrice: float}|null
     */
    public function extractConsumptionData(Request $request): ?array;

    /**
     * Extrait et décode les données JSON d'une requête
     * 
     * @param Request $request
     * @return array<string, mixed>
     */
    public function extractJsonData(Request $request): array;
}
