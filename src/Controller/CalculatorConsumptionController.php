<?php

namespace App\Controller\Energreen;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CalculatorConsumptionController extends AbstractController
{
    #[Route('/calculator_consumption', name: 'app_calculator_consumption')]
    public function index(Request $request): Response
    {
        $selectedIds = $request->get('appliances', []);

        $applianceMap = [
            1 => ['name' => 'Television', 'power' => 79, 'usage' => 6.76, 'mode' => 'hours_day'],
            2 => ['name' => 'Refrigerateur (Combiné)', 'power' => 40, 'usage' => 24, 'mode' => 'hours_day'],
            3 => ['name' => 'Congelateur Coffre', 'power' => 36, 'usage' => 24, 'mode' => 'hours_day'],
            4 => ['name' => 'Ordinateur de bureau', 'power' => 85, 'usage' => 3.9, 'mode' => 'hours_day'],
            5 => ['name' => 'Ordinateur portable', 'power' => 21, 'usage' => 3.22, 'mode' => 'hours_day'],
            6 => ['name' => 'Smartphone/tablette', 'power' => 10, 'usage' => 2, 'mode' => 'hours_day'],
            7 => ['name' => 'Modem DSL', 'power' => 11, 'usage' => 24, 'mode' => 'hours_day'],
            8 => ['name' => 'Decodeur', 'power' => 12, 'usage' => 19.7, 'mode' => 'hours_day'],
            9 => ['name' => 'Console de jeu', 'power' => 110, 'usage' => 2.7, 'mode' => 'hours_day'],
            10 => ['name' => 'Lave-linge', 'power' => 2000, 'usage' => 3.8, 'mode' => 'cycles_week', 'duration' => 0.275],
            11 => ['name' => 'Seche-linge', 'power' => 2500, 'usage' => 3.5, 'mode' => 'cycles_week', 'duration' => 0.65],
            12 => ['name' => 'Lave-vaisselle', 'power' => 1500, 'usage' => 3.2, 'mode' => 'cycles_week', 'duration' => 0.77],
            13 => ['name' => 'Four électrique', 'power' => 2500, 'usage' => 3.6, 'mode' => 'cycles_week', 'duration' => 0.31],
            14 => ['name' => 'Plaque de cuisson', 'power' => 2000, 'usage' => 7.9, 'mode' => 'cycles_week', 'duration' => 0.5],
            15 => ['name' => 'Chaudière', 'power' => 100, 'usage' => 5, 'mode' => 'hours_day'],
            16 => ['name' => 'Radiateur électrique', 'power' => 1500, 'usage' => 4, 'mode' => 'hours_day'],
            17 => ['name' => 'Ventilateur', 'power' => 35, 'usage' => 8, 'mode' => 'hours_day'],
            18 => ['name' => 'Eclairage (Ampoules)', 'power' => 100, 'usage' => 4, 'mode' => 'hours_day'],
        ];

        $appliances = [];
        if (is_array($selectedIds)) {
            foreach ($selectedIds as $id) {
                if (isset($applianceMap[$id])) {
                    $appliances[] = $applianceMap[$id];
                }
            }
        }

        return $this->render('CalculatorConsumption.html.twig', [
            'controller_name' => 'CalculatorConsumptionController',
            'appliances' => $appliances,
            'past_consumption' => $request->get('past_consumption'),
            'billing_date' => $request->get('billing_date'),
        ]);
    }
}
