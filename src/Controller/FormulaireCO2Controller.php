<?php
// src/Controller/FormulaireCO2Controller.php

namespace App\Controller;

use App\Entity\BilanCarbone;
use App\Service\BilanCarboneManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FormulaireCO2Controller extends AbstractController
{
    #[Route('/formulaire', name: 'app_formulaire', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $em, BilanCarboneManager $bilanManager): Response
    {
        $results = null;
        $total = 0;
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

            $scoreLogement = ((float) ($data['surface'] ?? 0) * (float) ($data['isolation_etat'] ?? 1) * (float) ($data['energie_principale'] ?? 0));
            $scoreLogement += (float) ($data['eau_chaude'] ?? 0) + (float) ($data['cuisson'] ?? 0) + (float) ($data['piscine'] ?? 0);

            $scoreNumerique = ((int) ($data['qty_smartphone'] ?? 0) * 80) + ((int) ($data['qty_laptop'] ?? 0) * 250);
            $scoreNumerique += ((float) ($data['heures_streaming'] ?? 0) * (float) ($data['reseau_type'] ?? 0) * 365);

            $scoreElectro = ((int) ($data['qty_refri'] ?? 0) * 300) + ((int) ($data['qty_lave_linge'] ?? 0) * 200);
            $scoreAlim = (float) ($data['regime_alimentaire'] ?? 2100) * (float) ($data['coeff_saison'] ?? 1);

            $scoreTransports = ((float) ($data['km_voiture'] ?? 0) * (float) ($data['vehicule_moteur'] ?? 0));
            $scoreTransports += ((int) ($data['vol_long'] ?? 0) * 1500);

            $scoreTextile = ((int) ($data['qty_jean'] ?? 0) * 25) + ((int) ($data['qty_chaussures'] ?? 0) * 15);

            $total = $scoreLogement + $scoreNumerique + $scoreElectro + $scoreAlim + $scoreTransports + $scoreTextile;

            // --- 3. ENREGISTREMENT DU NOUVEAU ---
            $bilan = new BilanCarbone();
            $bilan->setLogement(round($scoreLogement, 2));
            $bilan->setNumerique(round($scoreNumerique, 2));
            $bilan->setElectromenager(round($scoreElectro, 2));
            $bilan->setAlimentation(round($scoreAlim, 2));
            $bilan->setTransports(round($scoreTransports, 2));
            $bilan->setTextile(round($scoreTextile, 2));
            $bilan->setTotal(round($total, 2));

            // On utilise addBilanCarbone qui lie automatiquement l'utilisateur au bilan
            $user->addBilanCarbone($bilan);

            $em->persist($bilan);

            // Le flush unique va traiter : 
            // - L'insertion de l'archive (faite dans le manager)
            // - La suppression physique de l'ancien bilan (via removeBilanCarbone)
            // - L'insertion du nouveau bilan
            $em->flush();

            $results = [
                'Logement' => round($scoreLogement, 2),
                'Numérique' => round($scoreNumerique, 2),
                'Alimentation' => round($scoreAlim, 2),
                'Transports' => round($scoreTransports, 2),
                'Électroménager' => round($scoreElectro, 2),
                'Textile' => round($scoreTextile, 2),
            ];

            $this->addFlash('success', 'Votre bilan carbone a été mis à jour.');
        }

        return $this->render('formulaireCO2.html.twig', [
            'results' => $results,
            'total' => round($total, 2),
        ]);
    }
}