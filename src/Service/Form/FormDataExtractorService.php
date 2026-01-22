<?php

namespace App\Service\Form;

use Symfony\Component\HttpFoundation\Request;

class FormDataExtractorService implements FormDataExtractorServiceInterface
{
    public function extractProfileData(Request $request): array
    {
        return [
            'email' => $request->request->get('email'),
            'nom' => $request->request->get('nom'),
            'prenom' => $request->request->get('prenom'),
            'lodgment_type' => $request->request->get('lodgment_type'),
            'surface' => $request->request->get('surface'),
            'occupant' => $request->request->get('occupant'),
            'new_password' => $request->request->get('new_password'),
            'confirm_password' => $request->request->get('confirm_password'),
        ];
    }

    public function extractConsumptionData(Request $request): ?array
    {
        $totalKwh = (float) $request->request->get('total_kwh_input');
        $totalPrice = (float) $request->request->get('total_price_input');

        if ($totalKwh <= 0) {
            return null;
        }

        return [
            'totalKwh' => $totalKwh,
            'totalPrice' => $totalPrice
        ];
    }

    public function extractJsonData(Request $request): array
    {
        $data = json_decode($request->getContent(), true);
        return is_array($data) ? $data : [];
    }
}
