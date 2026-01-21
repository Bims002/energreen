<?php

namespace App\Controller;

use App\Service\Consumption\ConsumptionServiceInterface;
use App\Service\Form\FormDataExtractorServiceInterface;
use App\Service\Appliance\ApplianceDataServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CalculatorConsumptionController extends AbstractController
{
    public function __construct(
        private ConsumptionServiceInterface $consumptionService,
        private FormDataExtractorServiceInterface $formDataExtractor,
        private ApplianceDataServiceInterface $applianceDataService
    ) {
    }
    #[Route('/calculator_consumption', name: 'app_calculator_consumption', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user)
            return $this->redirectToRoute('app_login');

        if ($request->isMethod('POST')) {
            $consumptionData = $this->formDataExtractor->extractConsumptionData($request);

            if ($consumptionData) {
                // Délégation de la logique de sauvegarde au service
                $this->consumptionService->saveConsumption(
                    $user,
                    $consumptionData['totalKwh'],
                    $consumptionData['totalPrice']
                );

                $this->addFlash('success', 'Consommation mise à jour et archivée.');
                return $this->redirectToRoute('app_dashboard');
            }
        }

        // Récupération des données d'appareils
        $applianceData = $this->applianceDataService->getApplianceData($user);

        return $this->render('CalculatorConsumption.html.twig', [
            'lodgment' => $applianceData['lodgment'],
            'appliances' => $applianceData['userAppliances'],
            'allAppliances' => $applianceData['allAppliances'],
            'userApplianceNames' => $applianceData['userApplianceNames']
        ]);
    }
}