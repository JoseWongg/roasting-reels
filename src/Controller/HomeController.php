<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Movie;
use Knp\Component\Pager\PaginatorInterface;

/**
 * This controller is used to display the home page.
 */
class HomeController extends AbstractController
{
    /**
     * This method displays the home page.
     */
    #[Route('/', name: 'home')]
    public function index(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        // Get the search term
        $search = $request->query->get('search');
        // Get the movies
        $queryBuilder = $em->getRepository(Movie::class)->createQueryBuilder('m');

        // If there is a search term, search by title or IMDB ID
        if ($search) {
            // Search by title or IMDB ID (case-insensitive)
            $searchTerm = $search . '%';
            // Build the query
            $queryBuilder->where('LOWER(m.title) LIKE LOWER(:searchTerm) OR LOWER(m.imdbId) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', $searchTerm);
        }

        // Paginate the results
        $query = $queryBuilder->getQuery();
        $movies = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6 // Limit of 6 movies per page
        );

        // Render the home page
        return $this->render('home/index.html.twig', [
            'movies' => $movies,
            'search' => $search
        ]);
    }
}
