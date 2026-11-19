<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Pour vérifier si l'utilisateur est déjà connecté, on peut utiliser la méthode $this->getUser() de AbstractController
        if ($this->getUser()) {
            // Si l'utilisateur est deja connecté, alors je redirige sur l'accueil
            return $this->redirectToRoute('app_films');
        }
        // Sinon
        else {
            // On redirige sur la page de connexion
            return $this->render('security/login.html.twig', [
            // On récupere le dernier nom d'utilisateur connecté
            'last_username' => $authenticationUtils->getLastUsername(),
            // Et le dernier message d'erreur (s'il y en a un)
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);

        }
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
