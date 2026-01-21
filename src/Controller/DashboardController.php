<?php

namespace App\Controller;

use App\Service\Alert\AlertServiceInterface;
use App\Service\Carbon\CarbonServiceInterface;
use App\Service\Consumption\ConsumptionServiceInterface;
use App\Service\Dashboard\DashboardDataServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Consumption;
use App\Entity\Lodgment;
use App\Entity\BilanCarbone;

class DashboardController extends AbstractController
{
    public function __construct(
        private AlertServiceInterface $alertService,
        private CarbonServiceInterface $carbonService,
        private ConsumptionServiceInterface $consumptionService,
        private DashboardDataServiceInterface $dashboardDataService
    ) {
    }
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // 1. Vérification de la connexion
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // 2. Récupération des données de base
        $lodgmentRepo = $entityManager->getRepository(Lodgment::class);
        $consumptionRepo = $entityManager->getRepository(Consumption::class);
        $bilanRepo = $entityManager->getRepository(BilanCarbone::class);

        $userLodgment = $lodgmentRepo->findOneBy(['user' => $user]);

        $latestConsumption = $consumptionRepo->findOneBy(
            ['user' => $user],
            ['billing_date' => 'DESC']
        );

        $latestBilan = $bilanRepo->findOneBy(
            ['utilisateur' => $user],
            ['createdAt' => 'DESC']
        );

        // 3. Logique d'alerte mise à jour hebdomadaire
        $showUpdateReminder = $this->dashboardDataService->shouldShowUpdateReminder($latestConsumption);

        // 4. Préparation des données MOIS
        $chartData = $this->dashboardDataService->prepareMonthlyChartData($user);

        // 5. Calculs et variables de rendu
        $kwh = $latestConsumption ? $latestConsumption->getTotalKwh() : 0;
        $price = $latestConsumption ? $latestConsumption->getEstimatedPrice() : 0;
        $co2Emissions = $this->dashboardDataService->calculateCO2Emissions($kwh);
        $rating = $latestBilan ? $this->carbonService->calculateCarbonGrade($latestBilan->getTotal()) : ['label' => '?', 'color' => '#6c757d'];

        return $this->render('dashboard.html.twig', [
            // Données Graphique (Mois seulement)
            'labelsMois' => json_encode($chartData['labels']),
            'dataMois' => json_encode($chartData['data']),
            'infoMois' => "Historique basé sur vos 12 derniers relevés validés.",

            // Objets pour Twig (Indispensables pour vos conditions IF)
            'user_lodgment' => $userLodgment,
            'logement' => $userLodgment,
            'lodgment' => $userLodgment,
            'consumption' => $latestConsumption,
            'latest_consumption' => $latestConsumption,
            'latest_bilan' => $latestBilan,

            // Valeurs calculées
            'current_month_consumption' => $kwh,
            'current_month_cost' => $price,
            'co2_emissions' => $co2Emissions,
            'carbon_rating' => $rating,

            // États de l'interface
            'has_data' => ($latestConsumption !== null),
            'has_consumption' => ($latestConsumption !== null),
            'show_update_reminder' => $showUpdateReminder,

            // Suggestions complètes
            'suggestions_carbone' => $this->carbonService->generateDetailedSuggestions($latestBilan),
            'suggestions_elec' => $this->consumptionService->generateElectricSuggestions($latestConsumption),

            // Alertes Intelligentes (Nouvelle fonctionnalité)
            'smart_alerts' => $this->alertService->generateSmartAlerts($latestConsumption),
        ]);
    }
}
