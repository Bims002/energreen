<?php

namespace App\Controller;

use App\Service\Lodgment\LodgmentServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Lodgment;
use App\Entity\Appliance;

final class EnergreenController extends AbstractController
{
    public function __construct(
        private LodgmentServiceInterface $lodgmentService
    ) {
    }
    #[Route('/welcome', name: 'app_energreen_welcome')]
    public function welcome(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user)
            return $this->redirectToRoute('app_login');

        if ($request->isMethod('POST')) {
            try {
                $data = $request->request->all();

                // Création du logement via le service
                $this->lodgmentService->createLodgment($user, $data);

                // Création de la consommation initiale via le service
                $this->lodgmentService->createInitialConsumption($user, $data);

                $entityManager->flush();

                return $this->redirectToRoute('app_dashboard');
            } catch (\Exception $e) {
                dd("Erreur : " . $e->getMessage());
            }
        }
        return $this->render('welcome.html.twig');
    }

    #[Route('/appliance/toggle/{id}', name: 'appliance_toggle', methods: ['POST'])]
    public function toggleAppliance(Appliance $appliance, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $lodgment = $entityManager->getRepository(Lodgment::class)->findOneBy(['user' => $user], ['id' => 'DESC']);

        if ($lodgment) {
            $this->lodgmentService->toggleAppliance($lodgment, $appliance);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_calculator_consumption');
    }
}