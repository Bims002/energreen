<?php

namespace App\Controller;

use App\Entity\Lodgment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CalculatorConsumptionController extends AbstractController
{
    #[Route('/calculator_consumption', name: 'app_calculator_consumption')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupération du logement le plus récent de l'utilisateur
        $lodgment = $entityManager->getRepository(Lodgment::class)->findOneBy(
            ['user' => $user],
            ['id' => 'DESC']
        );

        // Récupération automatique des appareils via la table lodgment_appliances
        $appliances = $lodgment ? $lodgment->getAppliances() : [];

        return $this->render('CalculatorConsumption.html.twig', [
            'appliances' => $appliances,
        ]);
    }
}