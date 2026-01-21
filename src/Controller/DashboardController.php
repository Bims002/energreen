<?php

namespace App\Controller;

use App\Repository\ArchiveConsumptionRepository;
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
    public function index(ArchiveConsumptionRepository $archiveRepo, EntityManagerInterface $entityManager): Response
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
        $showUpdateReminder = false;
        if ($latestConsumption) {
            $lastDate = $latestConsumption->getBillingDate();
            $now = new \DateTime();
            $interval = $lastDate->diff($now);
            if ($interval->days >= 7) {
                $showUpdateReminder = true;
            }
        } else {
            $showUpdateReminder = true;
        }

        // 4. Préparation des données MOIS (Suppression JOUR et SEMAINE demandée)
        $archives = $archiveRepo->findBy(['user' => $user], ['archived_at' => 'ASC'], 12);
        $labelsMois = [];
        $dataMois = [];
        foreach ($archives as $archive) {
            $labelsMois[] = $archive->getArchivedAt()->format('d/m');
            $dataMois[] = $archive->getTotalKwh();
        }

        // 5. Calculs et variables de rendu
        $kwh = $latestConsumption ? $latestConsumption->getTotalKwh() : 0;
        $price = $latestConsumption ? $latestConsumption->getEstimatedPrice() : 0;
        $rating = $latestBilan ? $this->calculateCarbonGrade($latestBilan->getTotal()) : ['label' => '?', 'color' => '#6c757d'];

        return $this->render('dashboard.html.twig', [
            // Données Graphique (Mois seulement)
            'labelsMois' => json_encode($labelsMois),
            'dataMois' => json_encode($dataMois),
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
            'co2_emissions' => round($kwh * 0.367),
            'carbon_rating' => $rating,

            // États de l'interface
            'has_data' => ($latestConsumption !== null),
            'has_consumption' => ($latestConsumption !== null),
            'show_update_reminder' => $showUpdateReminder,

            // Suggestions complètes
            'suggestions_carbone' => $this->generateDetailedSuggestions($latestBilan),
            'suggestions_elec' => $this->generateElectricSuggestions($latestConsumption),

            // Alertes Intelligentes (Nouvelle fonctionnalité)
            'smart_alerts' => $this->generateSmartAlerts($latestConsumption),
        ]);
    }

    private function generateSmartAlerts(?Consumption $cons): array
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

    private function calculateCarbonGrade(float $total): array
    {
        if ($total <= 5000)
            return ['label' => 'A', 'color' => '#2ECC71'];
        if ($total <= 7000)
            return ['label' => 'B', 'color' => '#97D700'];
        if ($total <= 9000)
            return ['label' => 'C', 'color' => '#F4D03F'];
        if ($total <= 11000)
            return ['label' => 'D', 'color' => '#F39C12'];
        if ($total <= 13000)
            return ['label' => 'E', 'color' => '#E67E22'];
        return ['label' => 'F', 'color' => '#E74C3C'];
    }

    private function generateElectricSuggestions(?Consumption $cons): array
    {
        if (!$cons || $cons->getTotalKwh() <= 0)
            return [];

        $kwh = $cons->getTotalKwh();
        $suggestions = [];

        if ($kwh > 400) {
            $suggestions['Consommation'] = "⚡ Votre consommation est au-dessus de la moyenne. Pensez à débrancher les appareils en veille.";
        } else {
            $suggestions['Consommation'] = "💡 Votre consommation est maîtrisée. Continuez ainsi !";
        }

        $conseilsPlus = [
            "🔌 Utilisez des multiprises à interrupteur pour couper vos équipements la nuit.",
            "🧺 Privilégiez les heures creuses et les cycles 'Éco' pour votre lave-linge.",
            "💡 Si ce n'est pas déjà fait, passez toutes vos ampoules en LED.",
            "🧊 Dégivrez votre congélateur : 3mm de givre = 30% de consommation en plus !",
            "🥘 Couvrez vos casseroles pendant la cuisson pour économiser 25% d'énergie.",
            "🌡️ Réglez votre chauffe-eau entre 55°C et 60°C pour limiter l'entartrage et la conso.",
            "💻 Éteignez votre box internet la nuit : elle consomme autant qu'un petit frigo.",
            "🧼 Nettoyez la grille arrière de votre frigo pour faciliter l'évacuation de la chaleur.",
            "🚿 Installez un pommeau de douche économe pour réduire l'eau chaude à chauffer.",
            "🍞 Utilisez un grille-pain plutôt que le four pour réchauffer du pain."
        ];

        $indexRotation = floor(time() / 600) % count($conseilsPlus);
        $suggestions['Le conseil du moment'] = $conseilsPlus[$indexRotation];
        $indexRotation2 = (floor(time() / 600) + 1) % count($conseilsPlus);
        $suggestions['Astuce supplémentaire'] = $conseilsPlus[$indexRotation2];

        return $suggestions;
    }

    private function generateDetailedSuggestions(?BilanCarbone $bilan): array
    {
        if (!$bilan)
            return [];

        $suggestions = [];
        $timeIndex = (int) (time() / 600);

        // --- LOGEMENT ---
        $logementValue = $bilan->getLogement();
        if ($logementValue > 0) {
            if ($logementValue > 3000) {
                $options = [
                    "🏠 Impact élevé : L'isolation des combles peut réduire votre facture de 30%.",
                    "🏠 Alerte Énergie : Le double vitrage est indispensable pour stopper les pertes de chaleur.",
                    "🏠 Diagnostic : Vérifiez l'étanchéité de vos portes et fenêtres avec des joints isolants.",
                    "🏠 Chauffage : Une pompe à chaleur émet 3x moins de CO2 qu'une chaudière gaz."
                ];
                $suggestions['Logement'] = $options[$timeIndex % count($options)];
            } elseif ($logementValue > 1500) {
                $options = [
                    "🏠 Impact modéré : Baisser le chauffage de 1°C, c'est 7% d'économie sur l'année.",
                    "🏠 Astuce : Installez des thermostats connectés pour mieux réguler vos pièces.",
                    "🏠 Rappel : Fermez vos volets dès la tombée de la nuit pour garder la chaleur."
                ];
                $suggestions['Logement'] = $options[$timeIndex % count($options)];
            } else {
                $suggestions['Logement'] = "🏠 Excellent : Votre logement est une référence en efficacité !";
            }
        }

        // --- TRANSPORTS ---
        $transports = $bilan->getTransports();
        if ($transports > 0) {
            if ($transports > 4000) {
                $options = [
                    "🚗 Alerte : Le transport est votre point faible. Le train émet 80x moins que l'avion.",
                    "🚗 Mobilité : Avez-vous pensé au covoiturage pour vos trajets quotidiens ?",
                    "🚗 Conseil : Une voiture électrique diviserait par 3 votre impact transport.",
                    "🚗 Info : Réduire votre vitesse de 10km/h sur autoroute économise 1L/100km."
                ];
                $suggestions['Transports'] = $options[$timeIndex % count($options)];
            } elseif ($transports > 1500) {
                $options = [
                    "🚗 Impact moyen : Pour les trajets de moins de 5km, le vélo est plus rapide.",
                    "🚗 Astuce : L'éco-conduite (freinages souples) réduit la conso de 15%.",
                    "🚗 Idée : Testez les transports en commun au moins une fois par semaine."
                ];
                $suggestions['Transports'] = $options[$timeIndex % count($options)];
            } else {
                $suggestions['Transports'] = "🚲 Bravo : Votre mobilité est exemplaire et sobre.";
            }
        }

        // --- ALIMENTATION ---
        $alimentation = $bilan->getAlimentation();
        if ($alimentation > 0) {
            if ($alimentation > 2500) {
                $options = [
                    "🥗 Impact fort : Remplacer un bœuf par du poulet divise l'impact par 4.",
                    "🥗 Info : La viande rouge est responsable de 50% des émissions alimentaires.",
                    "🥗 Défi : Essayez de cuisiner végétarien 3 jours par semaine.",
                    "🥗 Astuce : Évitez les produits importés par avion."
                ];
                $suggestions['Alimentation'] = $options[$timeIndex % count($options)];
            } elseif ($alimentation > 1200) {
                $options = [
                    "🥗 Impact moyen : Privilégiez les circuits courts et les produits locaux.",
                    "🥗 Info : Les produits de saison ont une empreinte carbone 10x plus faible.",
                    "🥗 Conseil : Limitez le gaspillage alimentaire."
                ];
                $suggestions['Alimentation'] = $options[$timeIndex % count($options)];
            } else {
                $suggestions['Alimentation'] = "🌱 Top : Votre assiette est un véritable allié pour le climat.";
            }
        }

        // --- NUMÉRIQUE ---
        $num = $bilan->getNumerique();
        if ($num > 0) {
            if ($num > 800) {
                $options = [
                    "💻 Impact élevé : Le streaming en 4G consomme 20x plus que le Wi-Fi.",
                    "💻 Matériel : Garder son smartphone 4 ans au lieu de 2 divise son impact par 2.",
                    "💻 Stockage : Supprimez vos mails inutiles et vos vidéos sur le cloud.",
                    "💻 Astuce : Éteignez votre box internet la nuit."
                ];
                $suggestions['Numérique'] = $options[$timeIndex % count($options)];
            } else {
                $suggestions['Numérique'] = "💻 Sobriété : Votre usage numérique est responsable et maîtrisé.";
            }
        }

        // --- ÉLECTROMÉNAGER ---
        $electro = $bilan->getElectromenager();
        if ($electro > 0) {
            if ($electro > 400) {
                $options = [
                    "🔌 Conseil : Un lavage à 30°C consomme 3x moins qu'un cycle à 90°C.",
                    "🔌 Frigo : Dépoussiérer la grille arrière réduit sa conso de 10%.",
                    "🔌 Sèche-linge : C'est l'appareil le plus gourmand, privilégiez l'air libre.",
                    "🔌 Lave-vaisselle : Utilisez le mode 'Eco'."
                ];
                $suggestions['Électroménager'] = $options[$timeIndex % count($options)];
            } else {
                $suggestions['Électroménager'] = "🔌 Bien joué : Vos habitudes de lavage sont très économes.";
            }
        }

        // --- TEXTILE ---
        $textile = $bilan->getTextile();
        if ($textile > 0) {
            if ($textile > 500) {
                $options = [
                    "👕 Mode : La fabrication d'un jean nécessite 7500 litres d'eau.",
                    "👕 Conseil : Tournez-vous vers la seconde main (Vinted, Emmaüs).",
                    "👕 Info : Acheter 5 vêtements neufs de moins par an réduit l'impact de 200kg CO2.",
                    "👕 Entretien : Lavez moins souvent vos vêtements pour les faire durer."
                ];
                $suggestions['Textile'] = $options[$timeIndex % count($options)];
            } else {
                $suggestions['Textile'] = "👕 Durable : Vous privilégiez la qualité et la longévité.";
            }
        }

        return $suggestions;
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function showDashboard(ArchiveConsumptionRepository $archiveRepo, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // 1. Récupérations de base avec gestion d'erreurs
        $lodgment = $user->getLodgment();

        // Récupération sécurisée du dernier bilan
        $latestBilan = null;
        try {
            $bilansCarbone = $user->getBilansCarbone();
            if ($bilansCarbone && !$bilansCarbone->isEmpty()) {
                $latestBilan = $bilansCarbone->last();
            }
        } catch (\Exception $e) {
            // Si erreur, on récupère via le repository
            $bilanRepo = $entityManager->getRepository(BilanCarbone::class);
            $latestBilan = $bilanRepo->findOneBy(
                ['utilisateur' => $user],
                ['createdAt' => 'DESC']
            );
        }

        $consumption = $entityManager->getRepository(Consumption::class)->findOneBy(['user' => $user], ['billing_date' => 'DESC']);

        // --- NOUVELLE LOGIQUE : ALERTE MISE À JOUR HEBDOMADAIRE ---
        $showUpdateReminder = false;
        if ($consumption) {
            $lastDate = $consumption->getBillingDate();
            $now = new \DateTime();
            $interval = $lastDate->diff($now);

            // Si la dernière saisie date de plus de 7 jours
            if ($interval->days >= 7) {
                $showUpdateReminder = true;
            }
        } else {
            // Si aucune donnée n'existe encore
            $showUpdateReminder = true;
        }
        // ---------------------------------------------------------

        // 2. Préparation des données MOIS (garder votre code identique)
        $archives = $archiveRepo->findBy(['user' => $user], ['archived_at' => 'ASC'], 12);
        $labelsMois = [];
        $dataMois = [];
        foreach ($archives as $archive) {
            $labelsMois[] = $archive->getArchivedAt()->format('d/m');
            $dataMois[] = $archive->getTotalKwh();
        }

        // 3. Préparation des données JOUR (garder votre code identique)
        $labelsJour = [];
        $dataJour = [];
        $lastTotal = !empty($dataMois) ? end($dataMois) : 0;

        $joursFr = [
            'Mon' => 'Lun',
            'Tue' => 'Mar',
            'Wed' => 'Mer',
            'Thu' => 'Jeu',
            'Fri' => 'Ven',
            'Sat' => 'Sam',
            'Sun' => 'Dim'
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = new \DateTime("-$i days");
            $dayEn = $date->format('D');
            $dayFr = $joursFr[$dayEn];
            $labelsJour[] = $dayFr . ' ' . $date->format('d/m');
            $dataJour[] = round(($lastTotal / 30) * (rand(85, 115) / 100), 1);
        }

        // 4. Préparation des données SEMAINE (garder votre code identique)
        $dataSemaine = [
            round($lastTotal / 4.2, 1),
            round($lastTotal / 3.8, 1),
            round($lastTotal / 4.1, 1),
            round($lastTotal / 4, 1)
        ];

        // 5. Textes d'information contextuels
        $infoMois = "Historique basé sur vos 12 derniers relevés validés.";
        $infoSemaine = "Estimation de la répartition sur les 4 dernières semaines.";
        $infoJour = "Détail estimé du " . $labelsJour[0] . " au " . end($labelsJour) . ".";

        // 6. Calcul de la note Carbone
        $rating = $latestBilan ? $this->calculateCarbonGrade($latestBilan->getTotal()) : ['label' => '?', 'color' => '#6c757d'];

        return $this->render('dashboard.html.twig', [
            'labelsMois' => json_encode($labelsMois),
            'dataMois' => json_encode($dataMois),
            'labelsJour' => json_encode($labelsJour),
            'dataJour' => json_encode($dataJour),
            'dataSemaine' => json_encode($dataSemaine),

            'infoMois' => $infoMois,
            'infoSemaine' => $infoSemaine,
            'infoJour' => $infoJour,

            'lodgment' => $lodgment,
            'logement' => $lodgment,
            'user_lodgment' => $lodgment, // Ajout pour compatibilité
            'latest_bilan' => $latestBilan,
            'consumption' => $consumption,
            'latest_consumption' => $consumption, // Ajout pour compatibilité
            'carbon_rating' => $rating,
            'current_month_consumption' => $consumption ? $consumption->getTotalKwh() : 0,
            'current_month_cost' => $consumption ? $consumption->getEstimatedPrice() : 0,
            'co2_emissions' => $consumption ? round($consumption->getTotalKwh() * 0.367) : 0,
            'has_data' => ($consumption !== null),
            'has_consumption' => ($consumption !== null),
            'suggestions_carbone' => $this->generateDetailedSuggestions($latestBilan),
            'suggestions_elec' => $this->generateElectricSuggestions($consumption),

            // On envoie la variable à Twig
            'show_update_reminder' => $showUpdateReminder,
        ]);
    }
}