<?php

namespace App\Controller;
use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use App\Services\TmdbService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Services\Translator;

/**
 * This controller is used to display movie details.
 */
class MovieController extends AbstractController
{

    /**
     * This method displays the movie detail page.
     */
    #[Route('/movie/{id}/{page}', name: 'movie_detail', requirements: ['id' => '\d+', 'page' => '\d+'], defaults: ['page' => 1])]
    public function movieDetail(MovieRepository $movieRepository, ReviewRepository $reviewRepository, PaginatorInterface $paginator, TmdbService $tmdbService, int $id, int $page = 1): Response
    {
        $page = (int) $page;
        $movie = $movieRepository->find($id);

        if (!$movie) {
            throw $this->createNotFoundException('No movie found for id '.$id);
        }
        if ($movie->getImdbId()) {
            $youtubeId = $tmdbService->fetchYoutubeTrailerId($movie->getImdbId());
        }else
        {
            $youtubeId = null;
        }
        $queryBuilder = $reviewRepository->getReviewsByMovieQueryBuilder($id);
        $reviews = $paginator->paginate(
            $queryBuilder,
            $page,
            5 // Limit of 5 reviews per page
        );

        // Render the movie detail page
        return $this->render('movie/index.html.twig', [
            'movie' => $movie,
            'reviews' => $reviews,
            // Pass the YouTube ID to the template
            'youtubeId' => $youtubeId,
        ]);
    }


    /**
     * This method translates text to the specified language.
     */
    #[Route('/translate', name: 'translate_text', methods: ['POST'])]
    public function translate(Request $request, Translator $translator): Response
    {
        $data = json_decode($request->getContent(), true);
        $text = $data['text'] ?? '';
        $language = $data['language'] ?? '';

        // Call the Translator service's method to perform the translation
        $result = $translator->translateText($text, $language);

        if (isset($result['response'])) {
            return $this->json(['response' => $result['response']]);
        } else {
            // Handle errors appropriately
            return $this->json(['error' => 'Failed to translate text'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}