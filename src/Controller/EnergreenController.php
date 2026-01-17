<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Lodgment;
use App\Entity\Consumption;
use App\Entity\Appliance;

final class EnergreenController extends AbstractController
{
    #[Route('/welcome', name: 'app_energreen_welcome')]
    public function welcome(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user)
            return $this->redirectToRoute('app_login');

        if ($request->isMethod('POST')) {
            try {
                $lodgment = new Lodgment();
                $lodgment->setLodgmentType($request->request->get('lodgment_type'));
                $lodgment->setSurface((int) $request->request->get('surface'));
                $lodgment->setOccupant((int) $request->request->get('occupants'));
                $lodgment->setUser($user);

                $applianceIds = $request->request->all('appliances');
                foreach ($applianceIds as $id) {
                    $appliance = $entityManager->getRepository(Appliance::class)->find($id);
                    if ($appliance) {
                        $lodgment->addAppliance($appliance);
                    }
                }
                $entityManager->persist($lodgment);

                $consumption = new Consumption();
                $consumption->setPastConsumption((float) $request->request->get('past_consumption'));
                $consumption->setBillingDate(new \DateTime($request->request->get('billing_date')));
                $consumption->setUser($user);
                $entityManager->persist($consumption);

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
            if ($lodgment->getAppliances()->contains($appliance)) {
                $lodgment->removeAppliance($appliance);
            } else {
                $lodgment->addAppliance($appliance);
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_calculator_consumption');
    }
}