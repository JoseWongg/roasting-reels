<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewEditFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This controller is responsible for editing and deleting reviews.
 */
class ReviewEditController extends AbstractController
{






    #[Route('/review/edit/{id}', name: 'edit_review')]
    // Restricts route to ROLE_EDITOR users
    public function edit(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReviewEditFormType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if the 'save' button was clicked
            if ($form->get('save')->isClicked()) {
                $entityManager->flush();
                $this->addFlash('success', 'Review updated successfully.');
            }
            // Check if the 'delete' button was clicked
            elseif ($form->get('delete')->isClicked()) {
                $entityManager->remove($review);
                $entityManager->flush();
                $this->addFlash('success', 'Review deleted successfully.');
                return $this->redirectToRoute('movie_detail', ['id' => $review->getMovie()->getId()]);
            }

            return $this->redirectToRoute('movie_detail', ['id' => $review->getMovie()->getId()]);
        }

        return $this->render('review_edit/index.html.twig', [
            'form' => $form->createView(),
            'movieId' => $review->getMovie()->getId(),
        ]);
    }
}
