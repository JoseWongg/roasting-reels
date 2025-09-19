<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

//This project uses a service to hash passwords. See src/Services/PasswordHasher.php
//use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Services\PasswordHasher;
/**
 * This controller is used to register new users.
 */
class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     * This method registers a new user.
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, PasswordHasher $passwordHasher, EntityManagerInterface $entityManager,  LoggerInterface $logger    ): Response
{
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password
            $hashedPassword = $passwordHasher->hashPassword(
               $user, $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);


            // Check if the email domain is for editors
            if (str_ends_with($user->getEmail(), '@roastingreels.editor.com')) {
                $user->setRoles(['ROLE_EDITOR']);
            } else {
                $user->setRoles([]);
            }

            // Save the new user
            $entityManager->persist($user);
            $entityManager->flush();

            // Log the user registration
            $logger->info('New user registered', [
                'user' => $user->getId(),
            ]);

            // Redirect to the login
            return $this->redirectToRoute('app_login');
        }

        // Render the registration form
        return $this->render('registration/index.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}