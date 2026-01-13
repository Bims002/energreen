<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Consumption;
use App\Entity\Lodgment;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder au dashboard.');
            return $this->redirectToRoute('app_login');
        }

        // Récupérer les données de consommation de l'utilisateur
        $consumptionRepo = $entityManager->getRepository(Consumption::class);
        $latestConsumption = $consumptionRepo->findOneBy(
            ['user' => $user],
            ['billing_date' => 'DESC']
        );

        // Récupérer les données de logement de l'utilisateur
        $lodgmentRepo = $entityManager->getRepository(Lodgment::class);
        $userLodgment = $lodgmentRepo->findOneBy(['user' => $user]);

        // Vérifier si l'utilisateur a des données
        $hasData = $latestConsumption !== null && $userLodgment !== null;

        // Calculer les valeurs pour le dashboard (ou 0 si pas de données)
        $consumption = $latestConsumption ? $latestConsumption->getPastConsumption() : 0;
        $cost = $consumption * 0.17; // Approx 0.17€/kWh
        $co2Emissions = $consumption * 0.367; // Approx 367g CO2/kWh en France

        return $this->render('dashboard.html.twig', [
            'has_data' => $hasData,
            'co2_emissions' => round($co2Emissions),
            'current_month_consumption' => $consumption,
            'current_month_cost' => $cost,
            'user_lodgment' => $userLodgment,
            'latest_consumption' => $latestConsumption,
            'latest_bilan' => null, // À implémenter : bilan carbone complet
        ]);
    }

}
