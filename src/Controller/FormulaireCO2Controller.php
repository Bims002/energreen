<?php
// src/Controller/FormulaireCO2Controller.php

namespace App\Controller;

use App\Entity\BilanCarbone;
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
            // --- 1. ARCHIVAGE ---
            $oldBilan = $user->getBilanCarbone();
            if ($oldBilan) {
                // On archive les données via le service
                $bilanManager->archiveAndDeleteOldBilan($oldBilan);

                // CRUCIAL : On retire le bilan de la collection de l'utilisateur.
                // Comme nous avons configuré 'orphanRemoval: true' dans l'entité User,
                // Doctrine va supprimer ce bilan physiquement lors du prochain flush().
                $user->removeBilanCarbone($oldBilan);
            }

            // --- 2. CALCULS ---
            $data = $request->request->all();
            $scores = $this->bilanCalculator->calculateScores($data);

            // --- 3. ENREGISTREMENT DU NOUVEAU ---
            $bilan = $this->bilanCalculator->createBilanFromScores($scores);

            // On utilise addBilanCarbone qui lie automatiquement l'utilisateur au bilan
            $user->addBilanCarbone($bilan);

            $em->persist($bilan);

            // Le flush unique va traiter : 
            // - L'insertion de l'archive (faite dans le manager)
            // - La suppression physique de l'ancien bilan (via removeBilanCarbone)
            // - L'insertion du nouveau bilan
            $em->flush();

            $results = [
                'Logement' => $scores['logement'],
                'Numérique' => $scores['numerique'],
                'Alimentation' => $scores['alimentation'],
                'Transports' => $scores['transports'],
                'Électroménager' => $scores['electromenager'],
                'Textile' => $scores['textile'],
            ];

            $total = $scores['total'];

            $this->addFlash('success', 'Votre bilan carbone a été mis à jour.');
        }

        return $this->render('formulaireCO2.html.twig', [
            'results' => $results,
            'total' => round($total, 2),
        ]);
    }
}