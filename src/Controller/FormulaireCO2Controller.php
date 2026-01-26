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
                $bilanManager->archiveAndDeleteOldBilan($oldBilan);
                $user->removeBilanCarbone($oldBilan);
            }

            // --- 2. CALCULS ---
            $data = $request->request->all();

            $lodgment = $user->getLodgment();
            $data['occupant'] = $lodgment ? $lodgment->getOccupant() : 1;

            $scores = $this->bilanCalculator->calculateScores($data);

            // --- 3. ENREGISTREMENT DU NOUVEAU ---
            $bilan = $this->bilanCalculator->createBilanFromScores($scores);
            $user->addBilanCarbone($bilan);

            $em->persist($bilan);
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