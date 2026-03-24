<?php
// src/Controller/FormulaireCO2Controller.php

namespace App\Controller;

use App\Entity\BilanCarbone;
use App\Entity\ArchiveBilanCarbone; // Importation nécessaire
use App\Service\BilanCarboneManager;
use App\Service\BilanCarbone\BilanCarboneCalculatorServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FormulaireCO2Controller extends AbstractController
{
    public function __construct(
        private BilanCarboneCalculatorServiceInterface $bilanCalculator
    ) {
    }

    #[Route('/formulaire', name: 'app_formulaire', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $em, BilanCarboneManager $bilanManager): Response
    {
        $results = null;
        $total = 0;

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            // --- 1. NETTOYAGE / ARCHIVAGE DE L'ANCIEN ---
            $oldBilan = $user->getBilanCarbone();
            if ($oldBilan) {
                // Le service archive l'ancien et le supprime de la table bilan_carbone
                $bilanManager->archiveOldBilan($oldBilan);
            }

            // --- 2. CALCULS ---
            $data = $request->request->all();
            $lodgment = $user->getLodgment();
            $data['occupant'] = $lodgment ? $lodgment->getOccupant() : 1;

            $scores = $this->bilanCalculator->calculateScores($data);

            // --- 3. ENREGISTREMENT DU NOUVEAU BILAN ACTIF ---
            $bilan = $this->bilanCalculator->createBilanFromScores($scores);
            $user->addBilanCarbone($bilan);
            $em->persist($bilan);

            // --- 4. ARCHIVAGE DU NOUVEAU RÉSULTAT IMMÉDIATEMENT ---
            $archive = new ArchiveBilanCarbone();
            $archive->setUser($user);
            $archive->setScoreTotal($scores['total']);
            $archive->setCreatedAt(new \DateTimeImmutable());
            $archive->setDetails([
                'logement' => $scores['logement'],
                'numerique' => $scores['numerique'],
                'electromenager' => $scores['electromenager'],
                'alimentation' => $scores['alimentation'],
                'transports' => $scores['transports'],
                'textile' => $scores['textile'],
            ]);

            $em->persist($archive);

            // On flush tout (le nouveau bilan actif + l'archive du nouveau résultat)
            $em->flush();

            // --- 5. PRÉPARATION DE LA VUE ---
            $results = [
                'Logement' => $scores['logement'],
                'Numérique' => $scores['numerique'],
                'Alimentation' => $scores['alimentation'],
                'Transports' => $scores['transports'],
                'Électroménager' => $scores['electromenager'],
                'Textile' => $scores['textile'],
            ];

            $total = $scores['total'];

            $this->addFlash('success', 'Votre bilan carbone a été mis à jour et sauvegardé dans votre historique.');
        }

        return $this->render('formulaireCO2.html.twig', [
            'results' => $results,
            'total' => round($total, 2),
        ]);
    }
}