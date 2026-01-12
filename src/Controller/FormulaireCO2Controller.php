<?php
// src/Controller/FormulaireCO2Controller.php

namespace App\Controller;

use App\Entity\BilanCarbone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FormulaireCO2Controller extends AbstractController
{
    #[Route('/formulaire', name: 'app_formulaire', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')] // Seul un utilisateur connecté peut enregistrer
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $results = null;
        $total = 0;

        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            // 1. Calcul Logement
            $scoreLogement = ((float) ($data['surface'] ?? 0) * (float) ($data['isolation_etat'] ?? 1) * (float) ($data['energie_principale'] ?? 0));
            $scoreLogement += (float) ($data['eau_chaude'] ?? 0) + (float) ($data['cuisson'] ?? 0);
            $scoreLogement += (float) ($data['piscine'] ?? 0);

            // 2. Calcul Numérique
            $scoreNumerique = ((int) $data['qty_smartphone'] * 80) + ((int) $data['qty_laptop'] * 250);
            $scoreNumerique += ((float) ($data['heures_streaming'] ?? 0) * (float) ($data['reseau_type'] ?? 0) * 365);

            // 3. Calcul Électroménager
            $scoreElectro = ((int) $data['qty_refri'] * 300) + ((int) $data['qty_lave_linge'] * 200);

            // 4. Calcul Alimentation
            $scoreAlim = (float) ($data['regime_alimentaire'] ?? 2100) * (float) ($data['coeff_saison'] ?? 1);

            // 5. Calcul Transports
            $scoreTransports = ((float) ($data['km_voiture'] ?? 0) * (float) ($data['vehicule_moteur'] ?? 0));
            $scoreTransports += ((int) $data['vol_long'] * 1500);

            // 6. Calcul Textile
            $scoreTextile = ((int) $data['qty_jean'] * 25) + ((int) $data['qty_chaussures'] * 15);

            $total = $scoreLogement + $scoreNumerique + $scoreElectro + $scoreAlim + $scoreTransports + $scoreTextile;

            // --- ENREGISTREMENT ---
            $bilan = new BilanCarbone();
            $bilan->setLogement(round($scoreLogement, 2));
            $bilan->setNumerique(round($scoreNumerique, 2));
            $bilan->setElectromenager(round($scoreElectro, 2));
            $bilan->setAlimentation(round($scoreAlim, 2));
            $bilan->setTransports(round($scoreTransports, 2));
            $bilan->setTextile(round($scoreTextile, 2));
            $bilan->setTotal(round($total, 2));
            $bilan->setUtilisateur($this->getUser()); // Liaison avec l'User connecté

            $em->persist($bilan);
            $em->flush();

            $results = [
                'Logement' => round($scoreLogement, 2),
                'Numérique' => round($scoreNumerique, 2),
                'Alimentation' => round($scoreAlim, 2),
                'Transports' => round($scoreTransports, 2),
                'Électroménager' => round($scoreElectro, 2),
                'Textile' => round($scoreTextile, 2),
            ];
        }

        return $this->render('formulaireCO2.html.twig', [
            'results' => $results,
            'total' => round($total, 2),
        ]);
    }
}