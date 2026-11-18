<?php

namespace App\Controller;

use App\Entity\Realisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RealisateurController extends AbstractController
{
    #[Route('/realisateur/{id}', name: 'app_realisateur', requirements: ['id' => '\d+'])]
    public function show(Realisateur $realisateur): Response
    {
        return $this->render('realisateur/show.html.twig', [
            'realisateur' => $realisateur,
        ]);
    }
}