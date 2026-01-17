<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Lodgment;
use App\Entity\Appliance;

final class CalculatorConsumptionController extends AbstractController
{
    #[Route('/calculator_consumption', name: 'app_calculator_consumption')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // 1. Récupération du logement le plus récent de l'utilisateur
        $lodgment = $entityManager->getRepository(Lodgment::class)->findOneBy(
            ['user' => $user],
            ['id' => 'DESC']
        );

        // 2. Récupération des appareils que l'utilisateur possède déjà (pour l'affichage principal)
        $userAppliances = $lodgment ? $lodgment->getAppliances() : [];

        // 3. Extraction des NOMS des appareils possédés (pour cocher les cases dans la modale)
        $userApplianceNames = [];
        foreach ($userAppliances as $app) {
            $userApplianceNames[] = $app->getName();
        }

        // 4. Récupération de TOUS les appareils disponibles en base de données (pour la bibliothèque/modale)
        // Note : Assurez-vous d'avoir rempli votre table 'appliance' en BDD
        $allAppliancesFromDb = $entityManager->getRepository(Appliance::class)->findAll();

        return $this->render('CalculatorConsumption.html.twig', [
            'lodgment' => $lodgment,
            'appliances' => $userAppliances,           // Liste affichée sur la page
            'allAppliances' => $allAppliancesFromDb,   // Liste affichée dans la Pop-up
            'userApplianceNames' => $userApplianceNames // Utilisé pour le "checked"
        ]);
    }
}