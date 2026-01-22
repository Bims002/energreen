<?php

namespace App\Service\Consumption;

use App\Entity\ArchiveConsumption;
use App\Entity\Consumption;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ConsumptionService implements ConsumptionServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function generateElectricSuggestions(?Consumption $cons): array
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

    public function saveConsumption(User $user, float $totalKwh, float $totalPrice): Consumption
    {
        // 1. Chercher TOUTES les consommations existantes pour cet utilisateur
        $consumptions = $this->entityManager->getRepository(Consumption::class)->findBy(['user' => $user]);

        if (!empty($consumptions)) {
            // On prend la plus récente pour l'archivage
            $latest = end($consumptions);

            if ($latest->getTotalKwh() > 0) {
                $archive = new ArchiveConsumption();
                $archive->setUser($user);
                $archive->setTotalKwh($latest->getTotalKwh());
                $archive->setEstimatedPrice($latest->getEstimatedPrice());
                $this->entityManager->persist($archive);
            }

            // 2. SUPPRIMER TOUTES les anciennes lignes pour éviter les doublons
            foreach ($consumptions as $oldConso) {
                $this->entityManager->remove($oldConso);
            }
            // On flush les suppressions avant de recréer la ligne propre
            $this->entityManager->flush();
        }

        // 3. Créer la ligne UNIQUE et propre
        $consumption = new Consumption();
        $consumption->setUser($user);
        $consumption->setTotalKwh($totalKwh);
        $consumption->setEstimatedPrice($totalPrice);
        $consumption->setBillingDate(new \DateTime());
        $consumption->setPastConsumption(0);

        $this->entityManager->persist($consumption);
        $this->entityManager->flush();

        return $consumption;
    }
}
