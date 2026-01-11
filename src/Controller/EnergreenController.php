<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Lodgment;
use App\Entity\Consumption;

use App\Form\WelcomeFormType;

final class EnergreenController extends AbstractController
{
    #[Route('/welcome', name: 'app_energreen_welcome')]
    public function welcome(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Créer le formulaire
        $form = $this->createForm(WelcomeFormType::class);
        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Créer et persister Lodgment
            $lodgment = new Lodgment();
            $lodgment->setLodgmentType($data['lodgment_type']);
            $lodgment->setSurface($data['surface']);
            $lodgment->setOccupant($data['occupants']);
            $lodgment->setUser($user);
            $entityManager->persist($lodgment);

            // Créer et persister Consumption
            $consumption = new Consumption();
            $consumption->setPastConsumption($data['past_consumption']);
            $consumption->setBillingDate(new \DateTime($data['billing_date']));
            $consumption->setUser($user);
            $entityManager->persist($consumption);

            $entityManager->flush();

            $this->addFlash('success', 'Vos données ont été enregistrées avec succès !');
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('welcome.html.twig', [
            'welcomeForm' => $form,
        ]);
    }
}
