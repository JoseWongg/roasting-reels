<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * This class login in and out users.
 */
class SecurityController extends AbstractController
{
    /**
     * This method handles the login.
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Render the login form
        return $this->render('security/index.html.twig', [

            'controller_name' => 'SecurityController',
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    // This route is handled by the Symfony's Security system.
    // Controller can be blank: it will never be called as the route is handled by the Symfony's Security system.
    // Symfony will intercept this route and handle the logout automatically.
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // throw new \LogicException('This method can be blank - it will be intercepted by the logout key on the firewall.');
        // Message to display if the logout route is not activated in security.yaml
        //throw new \Exception('Do not forget to activate logout in security.yaml');
    }
}
