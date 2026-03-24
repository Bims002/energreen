<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\BilanCarboneManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ArchiCarboneController extends AbstractController
{
    #[Route('/recalculer', name: 'app_bilan_recalculate')]
    public function recalculate(BilanCarboneManager $bilanManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // On récupère le bilan actuel (l'unique)
        $oldBilan = $user->getBilanCarbone();

        if ($oldBilan) {
            // Cette méthode archive dans archive_bilan_carbone 
            // ET supprime dans bilan_carbone
            $bilanManager->archiveOldBilan($oldBilan);
        }

        // On redirige vers le formulaire pour créer le NOUVEAU bilan unique
        return $this->redirectToRoute('app_bilan_new');
    }
}