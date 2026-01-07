<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Lodgment;
use App\Entity\Consumption;

final class EnergreenController extends AbstractController
{
    #[Route('/welcome', name: 'app_energreen_welcome')]
    public function welcome(): Response
    {
        return $this->render('welcome.html.twig', [
            'controller_name' => 'EnergreenController',
        ]);
    }

    #[Route('/welcome/submit', name: 'app_welcome_submit', methods: ['POST'])]
    public function submitWelcome(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour soumettre ce formulaire.');
            return $this->redirectToRoute('app_login');
        }

        // Récupérer les données du formulaire
        $lodgmentType = $request->request->get('lodgment_type');
        $surface = $request->request->get('surface');
        $occupants = $request->request->get('occupants');
        $pastConsumption = $request->request->get('past_consumption');
        $billingDate = $request->request->get('billing_date');

        // Créer et persister Lodgment
        $lodgment = new Lodgment();
        $lodgment->setLodgmentType($lodgmentType ?? 'Non spécifié');
        $lodgment->setSurface((int) $surface ?: 0);
        $lodgment->setOccupant((int) $occupants ?: 1);
        $lodgment->setUser($user); // Associer l'utilisateur

        $entityManager->persist($lodgment);

        // Créer et persister Consumption si les données sont présentes
        if ($pastConsumption && $billingDate) {
            $consumption = new Consumption();
            $consumption->setPastConsumption((int) $pastConsumption);
            $consumption->setBillingDate(new \DateTime($billingDate));
            $consumption->setUser($user); // Associer l'utilisateur

            $entityManager->persist($consumption);
        }

        $entityManager->flush();

        // Ajouter un message flash de succès
        $this->addFlash('success', 'Vos informations ont bien été enregistrées !');

        // Rediriger vers le dashboard
        return $this->redirectToRoute('app_dashboard');
    }
}
