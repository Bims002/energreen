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
        $user = $this->getUser();

        // Vérifie si dans ton entité User la méthode s'appelle getBilanCarbone() ou getBilansCarbone()
        $oldBilan = $user->getBilanCarbone();

        if ($oldBilan) {
            $bilanManager->archiveAndDeleteOldBilan($oldBilan);
        }

        return $this->redirectToRoute('app_bilan_new');
    }
}