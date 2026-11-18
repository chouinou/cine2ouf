<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Seance;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReservationController extends AbstractController
{
    #[Route('/seance/{id}/reserver', name: 'app_reservation', requirements: ['id' => '\d+'])]
    public function new(Seance $seance, Request $request, EntityManagerInterface $em): Response
    {
        // 1. On refuse les séances passées et les séances complètes
        if ($seance->getDateHeur() < new \DateTimeImmutable() || $seance->estComplete()) {
            $this->addFlash('erreur', 'Cette séance n\'est plus disponible à la réservation.');

            return $this->redirectToRoute('app_film', ['id' => $seance->getFilm()->getId()]);
        }

        // 2. On prépare une réservation, déjà reliée à la séance et datée
        $reservation = new Reservation();
        $reservation->setSeance($seance);
        $reservation->setDateReservation(new \DateTimeImmutable());

        // 3. On crée le formulaire et on lui donne la requête
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        // 4. Si le formulaire est envoyé et respecte les règles de validation
        if ($form->isSubmitted() && $form->isValid()) {

            // 5. La vérification métier : assez de places ?
            if ($reservation->getNbPlace() > $seance->getPlacesRestantes()) {
                $form->get('nbPlace')->addError(new FormError(
                    'Il ne reste que ' . $seance->getPlacesRestantes() . ' places pour cette séance.'
                ));
            } else {
                // 6. On enregistre, on prévient, on redirige
                $em->persist($reservation);
                $em->flush();

                $this->addFlash('succes', 'Votre réservation de ' . $reservation->getNbPlace() . ' place(s) est confirmée.');

                return $this->redirectToRoute('app_film', ['id' => $seance->getFilm()->getId()]);
            }
        }

        // 7. Premier affichage, ou formulaire avec des erreurs
        return $this->render('reservation/new.html.twig', [
            'seance' => $seance,
            'form' => $form,
        ]);
    }
}