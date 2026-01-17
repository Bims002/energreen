<?php

namespace App\Controller;

use App\Entity\ArchiveConsumption;
use App\Entity\Consumption;
use App\Entity\Lodgment;
use App\Entity\Appliance;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CalculatorConsumptionController extends AbstractController
{
    #[Route('/calculator_consumption', name: 'app_calculator_consumption', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user)
            return $this->redirectToRoute('app_login');

        if ($request->isMethod('POST')) {
            $totalKwh = (float) $request->request->get('total_kwh_input');
            $totalPrice = (float) $request->request->get('total_price_input');

            if ($totalKwh > 0) {
                // 1. Chercher TOUTES les consommations existantes pour cet utilisateur pour nettoyer les doublons
                $consumptions = $entityManager->getRepository(Consumption::class)->findBy(['user' => $user]);

                if (!empty($consumptions)) {
                    // On prend la plus récente pour l'archivage
                    $latest = end($consumptions);

                    if ($latest->getTotalKwh() > 0) {
                        $archive = new ArchiveConsumption();
                        $archive->setUser($user);
                        $archive->setTotalKwh($latest->getTotalKwh());
                        $archive->setEstimatedPrice($latest->getEstimatedPrice());
                        $entityManager->persist($archive);
                    }

                    // 2. SUPPRIMER TOUTES les anciennes lignes pour éviter les doublons vus sur votre image
                    foreach ($consumptions as $oldConso) {
                        $entityManager->remove($oldConso);
                    }
                    // On flush les suppressions avant de recréer la ligne propre
                    $entityManager->flush();
                }

                // 3. Créer la ligne UNIQUE et propre
                $consumption = new Consumption();
                $consumption->setUser($user);
                $consumption->setTotalKwh($totalKwh);
                $consumption->setEstimatedPrice($totalPrice);
                $consumption->setBillingDate(new \DateTime());
                $consumption->setPastConsumption(0);

                $entityManager->persist($consumption);
                $entityManager->flush();

                $this->addFlash('success', 'Consommation mise à jour et archivée.');
                return $this->redirectToRoute('app_dashboard');
            }
        }

        // --- PARTIE AFFICHAGE (Inchangée) ---
        $lodgment = $entityManager->getRepository(Lodgment::class)->findOneBy(['user' => $user], ['id' => 'DESC']);
        $userAppliances = $lodgment ? $lodgment->getAppliances() : [];
        $userApplianceNames = array_map(fn($app) => $app->getName(), $userAppliances->toArray());
        $allAppliancesFromDb = $entityManager->getRepository(Appliance::class)->findAll();

        return $this->render('CalculatorConsumption.html.twig', [
            'lodgment' => $lodgment,
            'appliances' => $userAppliances,
            'allAppliances' => $allAppliancesFromDb,
            'userApplianceNames' => $userApplianceNames
        ]);
    }
}