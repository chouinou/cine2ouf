<?php

namespace App\Controller;

use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SalleController extends AbstractController
{
    #[Route('/salles', name: 'app_salles')]
    public function index(SalleRepository $salleRepository): Response
    {
        return $this->render('salle/index.html.twig', [
            'salles' => $salleRepository->findBy([], ['capacite' => 'DESC']),
            'capaciteTotale' => $salleRepository->getCapaciteTotale(),
        ]);
    }
}