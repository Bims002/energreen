<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Consumption;
use App\Entity\Lodgment;
use App\Entity\BilanCarbone;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user)
            return $this->redirectToRoute('app_login');

        $consumptionRepo = $entityManager->getRepository(Consumption::class);
        $latestConsumption = $consumptionRepo->findOneBy(['user' => $user], ['billing_date' => 'DESC']);

        $lodgmentRepo = $entityManager->getRepository(Lodgment::class);
        $userLodgment = $lodgmentRepo->findOneBy(['user' => $user]);

        $bilanRepo = $entityManager->getRepository(BilanCarbone::class);
        $latestBilan = $bilanRepo->findOneBy(['utilisateur' => $user], ['createdAt' => 'DESC']);

        $consumptionValue = $latestConsumption ? $latestConsumption->getPastConsumption() : 0;

        // --- AJOUT UNIQUEMENT DE LA LOGIQUE DE NOTE ---
        $rating = ['label' => '?', 'color' => '#6c757d'];
        if ($latestBilan) {
            $rating = $this->calculateCarbonGrade($latestBilan->getTotal());
        }
        // ----------------------------------------------

        return $this->render('dashboard.html.twig', [
            'has_data' => ($latestConsumption !== null),
            'co2_emissions' => round($consumptionValue * 0.367),
            'current_month_consumption' => $consumptionValue,
            'current_month_cost' => $consumptionValue * 0.17,
            'user_lodgment' => $userLodgment,
            'latest_consumption' => $latestConsumption,
            'latest_bilan' => $latestBilan,
            'carbon_rating' => $rating, // Variable ajoutée
            'suggestions_carbone' => $this->generateDetailedSuggestions($latestBilan),
            'suggestions_elec' => $this->generateElectricSuggestions($latestConsumption),
        ]);
    }

    // --- AJOUT DE LA MÉTHODE DE CALCUL ---
    private function calculateCarbonGrade(float $total): array
    {
        if ($total <= 2000)
            return ['label' => 'A', 'color' => '#2ECC71'];
        if ($total <= 5000)
            return ['label' => 'B', 'color' => '#97D700'];
        if ($total <= 8000)
            return ['label' => 'C', 'color' => '#F4D03F'];
        if ($total <= 10000)
            return ['label' => 'D', 'color' => '#F39C12'];
        if ($total <= 12000)
            return ['label' => 'E', 'color' => '#E67E22'];
        return ['label' => 'F', 'color' => '#E74C3C'];
    }

    private function generateElectricSuggestions(?Consumption $cons): array
    {
        if (!$cons || $cons->getPastConsumption() <= 0)
            return [];

        $kwh = $cons->getPastConsumption();
        $suggestions = [];

        if ($kwh > 400) {
            $suggestions['Consommation'] = "⚡ Votre consommation est au-dessus de la moyenne. Pensez à débrancher les appareils en veille.";
        } else {
            $suggestions['Consommation'] = "💡 Votre consommation est maîtrisée. Continuez ainsi !";
        }

        $suggestions['Équipements'] = "🔌 Utilisez des multiprises à interrupteur pour couper vos équipements la nuit.";
        $suggestions['Lavage'] = "🧺 Privilégiez les heures creuses et les cycles 'Éco' pour votre lave-linge.";
        $suggestions['Éclairage'] = "💡 Si ce n'est pas déjà fait, passez toutes vos ampoules en LED.";

        return $suggestions;
    }

    private function generateDetailedSuggestions(?BilanCarbone $bilan): array
    {
        if (!$bilan)
            return [];

        $suggestions = [];

        // Logement
        $logement = $bilan->getLogement();
        if ($logement > 0) {
            if ($logement > 3000) {
                $suggestions['Logement'] = "🏠 Impact élevé : Pensez à l'isolation des combles ou au double vitrage.";
            } elseif ($logement > 1500) {
                $suggestions['Logement'] = "🏠 Impact modéré : Baissez le chauffage de 1°C pour économiser 7%.";
            } else {
                $suggestions['Logement'] = "🏠 Excellent : Votre logement consomme peu.";
            }
        }

        // Transports
        $transports = $bilan->getTransports();
        if ($transports > 0) {
            if ($transports > 4000) {
                $suggestions['Transports'] = "🚗 Alerte : Le transport est votre plus gros poste. Privilégiez le train.";
            } elseif ($transports > 1500) {
                $suggestions['Transports'] = "🚗 Impact moyen : Avez-vous pensé au vélo électrique pour les petits trajets ?";
            } else {
                $suggestions['Transports'] = "🚲 Bravo : Votre mobilité est exemplaire.";
            }
        }

        // Alimentation
        $alimentation = $bilan->getAlimentation();
        if ($alimentation > 0) {
            if ($alimentation > 2500) {
                $suggestions['Alimentation'] = "🥗 Impact fort : Réduire la viande rouge est le levier le plus efficace.";
            } elseif ($alimentation > 1200) {
                $suggestions['Alimentation'] = "🥗 Impact modéré : Privilégiez les fruits et légumes de saison.";
            } else {
                $suggestions['Alimentation'] = "🌱 Top : Votre alimentation est respectueuse.";
            }
        }

        // Numérique
        $num = $bilan->getNumerique();
        if ($num > 0) {
            if ($num > 800) {
                $suggestions['Numérique'] = "💻 Impact élevé : Évitez le streaming 4K et gardez vos appareils plus longtemps.";
            } else {
                $suggestions['Numérique'] = "💻 Sobriété numérique : Bonne gestion de vos équipements.";
            }
        }

        // Électroménager
        $electro = $bilan->getElectromenager();
        if ($electro > 0) {
            if ($electro > 400) {
                $suggestions['Électroménager'] = "🔌 Conseil : Privilégiez les cycles 'Éco' à 30°C.";
            } else {
                $suggestions['Électroménager'] = "🔌 Bien joué : Vos habitudes sont économes.";
            }
        }

        // Textile
        $textile = $bilan->getTextile();
        if ($textile > 0) {
            if ($textile > 500) {
                $suggestions['Textile'] = "👕 Mode : Votre impact est notable. Pensez à la seconde main.";
            } else {
                $suggestions['Textile'] = "👕 Durable : Vous privilégiez la qualité à la quantité.";
            }
        }

        return $suggestions;
    }
}