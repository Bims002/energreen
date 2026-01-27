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
use App\Entity\ArchiveBilanCarbone; // <--- VÉRIFIEZ BIEN CE NOM

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
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupération sécurisée des Repositories
        $lodgmentRepo = $entityManager->getRepository(Lodgment::class);
        $consumptionRepo = $entityManager->getRepository(Consumption::class);
        $bilanRepo = $entityManager->getRepository(BilanCarbone::class);

        // Initialisation par défaut pour éviter que Twig ne plante si l'entité archive a un souci
        $labelsBilan = [];
        $dataBilanTotal = [];

        try {
            $archiveRepo = $entityManager->getRepository(ArchiveBilanCarbone::class);
            $archives = $archiveRepo->findBy(['user' => $user], ['createdAt' => 'ASC']);
            foreach ($archives as $archive) {
                $labelsBilan[] = $archive->getCreatedAt() ? $archive->getCreatedAt()->format('d/m/Y') : '';
                $dataBilanTotal[] = $archive->getScoreTotal() ?? 0;
            }
        } catch (\Exception $e) {
            // Si l'entité Archive n'existe pas encore ou bug, on laisse les tableaux vides
            // Cela empêche toute la page de disparaître
        }

        $userLodgment = $lodgmentRepo->findOneBy(['user' => $user]);

        $latestConsumption = $consumptionRepo->findOneBy(
            ['user' => $user],
            ['billing_date' => 'DESC']
        );

        $latestBilan = $bilanRepo->findOneBy(
            ['utilisateur' => $user],
            ['createdAt' => 'DESC']
        );

        $showUpdateReminder = $this->dashboardDataService->shouldShowUpdateReminder($latestConsumption);
        $chartData = $this->dashboardDataService->prepareMonthlyChartData($user);

        // 1. On récupère le volume en s'assurant qu'il soit float (0.0 si null)
        $kwh = $latestConsumption ? (float) $latestConsumption->getTotalKwh() : 0.0;

        // 2. On récupère le prix
        $price = $latestConsumption ? $latestConsumption->getEstimatedPrice() : 0.0;

        // 3. On appelle le service avec la valeur sécurisée
        $co2Emissions = $this->dashboardDataService->calculateCO2Emissions($kwh);

        // 4. Le reste de ton code (Rating)
        $rating = $latestBilan ? $this->carbonService->calculateCarbonGrade($latestBilan->getTotal()) : ['label' => '?', 'color' => '#6c757d'];

        return $this->render('dashboard.html.twig', [
            'labelsMois' => json_encode($chartData['labels'] ?? []),
            'dataMois' => json_encode($chartData['data'] ?? []),
            'infoMois' => !empty($chartData['data']) ? "Consommation sur les 12 derniers relevés" : "Aucun historique disponible",

            'user_lodgment' => $userLodgment,
            'logement' => $userLodgment,
            'lodgment' => $userLodgment,
            'consumption' => $latestConsumption,
            'latest_consumption' => $latestConsumption,
            'latest_bilan' => $latestBilan,

            'current_month_consumption' => $kwh,
            'current_month_cost' => $price,
            'co2_emissions' => $co2Emissions,
            'carbon_rating' => $rating,

            'has_data' => ($latestConsumption !== null),
            'has_consumption' => ($latestConsumption !== null),
            'show_update_reminder' => $showUpdateReminder,

            'suggestions_carbone' => $this->carbonService->generateDetailedSuggestions($latestBilan),
            'suggestions_elec' => $this->consumptionService->generateElectricSuggestions($latestConsumption),
            'smart_alerts' => $this->alertService->generateSmartAlerts($latestConsumption),

            // On garantit que ces variables arrivent à Twig, même vides
            'labelsBilan' => json_encode($labelsBilan),
            'dataBilanTotal' => json_encode($dataBilanTotal),
        ]);
    }
}