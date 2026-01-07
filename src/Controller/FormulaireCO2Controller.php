<?php

namespace App\Controller;

use App\Service\CarbonCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormulaireCO2Controller extends AbstractController
{
    #[Route('/formulaire', name: 'app_formulaire', methods: ['GET', 'POST'])]
    public function formulaire(Request $request, CarbonCalculator $calculator): Response
    {
        $results = null;
        $total = 0;

        if ($request->isMethod('POST')) {
            $results = $calculator->calculateAll($request->request->all());
            $total = array_sum($results);
        }

        return $this->render('formulaireCO2.html.twig', [
            'results' => $results,
            'total' => $total
        ]);
    }
}

