<?php

namespace App\Controller;
use App\Services\TmdbService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This controller is used for testing purposes only. It tests the TMDB API and the database connection.
 * See the testInsertMovie() method in the TmdbService class for more details.
 */
class TestController extends AbstractController
{
    /**
     * This method tests the TMDB API.
     */
    #[Route('/test-insert-movie', name: 'test_insert_movie')]
    public function testInsertMovie(TmdbService $tmdbService): Response
    {
        $movie = $tmdbService->testInsertMovie();

        if ($movie) {
            return new Response('Test movie inserted with ID: ' . $movie->getId());
        } else {
            return new Response('Failed to insert test movie.');
        }
    }
}
