<?php

namespace App\Controller;

use App\Entity\Film;
use App\Repository\FilmRepository;
use App\Repository\SeanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FilmController extends AbstractController
{
    #[Route('/', name: 'app_films')]
    public function index(FilmRepository $filmRepository): Response
    {
        return $this->render('film/index.html.twig', [
            'films' => $filmRepository->findALaffiche(),
        ]);
    }

    #[Route('/film/{id}', name: 'app_film', requirements: ['id' => '\d+'])]
    public function show(Film $film, SeanceRepository $seanceRepository): Response
    {
        // On range les séances dans un tableau dont chaque clé est un jour
        $seancesParJour = [];

        foreach ($seanceRepository->findProchainesPourFilm($film) as $seance) {
            $jour = $seance->getDateHeur()->format('Y-m-d');
            $seancesParJour[$jour][] = $seance;
        }

        return $this->render('film/show.html.twig', [
            'film' => $film,
            'seancesParJour' => $seancesParJour,
        ]);
    }
}