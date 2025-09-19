<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Review;
use App\Entity\Movie;
use App\Form\ReviewFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;

// Include the validator interface to validate score is between 1 and 5
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

// Include the logger interface
use Psr\Log\LoggerInterface;

/**
 * This controller is used to enable users to add reviews.
 */
class ReviewController extends AbstractController
{
    /**
     * This method displays the review form and handles the form submission.
     */
    #[Route('/movie/{id}/add-review', name: 'add_review')]
    public function addReview(Request $request, Movie $movie, EntityManagerInterface $entityManager, ValidatorInterface $validator, LoggerInterface $logger): Response
    {
        // Get the logged-in user
        $user = $this->getUser();

        // Redirect to the login page if the user is not logged in
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        // Create the review form
        $review = new Review();
        $form = $this->createForm(ReviewFormType::class, $review);

        // Handle the form submission
        $form->handleRequest($request);

        // Validate the form
        if ($form->isSubmitted() && $form->isValid()) {

            // Perform manual validation on $review->getScore()
            $scoreConstraint = new Assert\Range([
                'min' => 1,
                'max' => 5,
                'notInRangeMessage' => 'Score must be between {{ min }} and {{ max }}',
            ]);
            $scoreErrors = $validator->validate($review->getScore(), $scoreConstraint);

            if (count($scoreErrors) > 0) {
                // If there are errors, convert them to a string (or handle them as you see fit)
                $errorsString = (string) $scoreErrors;

                // DEVELOPER : You can either add a flash message with the error...
                $this->addFlash('error', $errorsString);

                // DEVELOPER: ...or directly return a response with the error message.
                // return new Response($errorsString, Response::HTTP_BAD_REQUEST);

                // Redirect to the add review page
                return $this->redirectToRoute('add_review', ['id' => $movie->getId()]);
            }

            $review->setUser($user);
            $review->setMovie($movie);
            $review->setDate(new \DateTime());

            // Save the new review
            $entityManager->persist($review);
            $entityManager->flush();

            // Log the review creation
            // See var/log/dev.log for the output
            // Example: [2021-03-15T15:12:28.000000+00:00] app.INFO: New review added {"user":1,"movie":"Gladiator","review":1}
            $logger->info('New review added', [
                'user' => $user->getId(),
                'movie' => $movie->getTitle(),
                'review' => $review->getId(),
            ]);

            // Set a success flash message. Its behavior is defined in templates/base.html.twig using a javascript function.
            $this->addFlash('success', 'Your review has been submitted successfully.');

            // Redirect to the movie detail page
            return $this->redirectToRoute('movie_detail', ['id' => $movie->getId()]);
        }

        // Render the review form
        return $this->render('review/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
