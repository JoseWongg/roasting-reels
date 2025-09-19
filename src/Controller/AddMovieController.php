<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Form\AddMovieFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
// This component is used to slugify the filename (convert it to a URL-friendly and valid filename)
use Symfony\Component\String\Slugger\SluggerInterface;
use Psr\Log\LoggerInterface;

use App\Services\TmdbService;

use App\Services\Translator;

/**
 * This class is used to add a movie.
 */
class AddMovieController extends AbstractController
{

    private TmdbService $tmdbService;
    private Translator $translator;



    // Inject the TmdbService into the controller
    public function __construct(TmdbService $tmdbService, Translator $translator)
    {
        // Inject the TmdbService into the controller
        $this->tmdbService = $tmdbService;
        // Inject the Translator service into the controller
        $this->translator = $translator;
    }


    // The route for adding a movie
    #[Route('/add-movie', name: 'add_movie')]
    public function add(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, LoggerInterface $logger ): Response
    {
        // Create a new Movie object
        $movie = new Movie();
        // Create a form for adding a movie
        $form = $this->createForm(AddMovieFormType::class, $movie);
        // Handle the form submission
        $form->handleRequest($request);

        // If the form is submitted and valid, save the movie
        if ($form->isSubmitted() && $form->isValid()) {

            // Get the poster file from the form
            $file = $form['poster']->getData();
            // If a file is uploaded
            if ($file) {
                // Get the original filename
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of a URL or filename. It ensures that the filename is valid and secure
                $safeFilename = $slugger->slug($originalFilename);
                // this ensures that the filename is unique and avoids overwriting any existing files with the same name
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    // Moves the file to the directory where posters are stored
                    $file->move(
                        // The directory where the file is stored
                        $this->getParameter('posters_directory'),
                        // The new filename
                        $newFilename
                    );
                    // Catch any file exceptions
                } catch (FileException $e) {

                    // Add a flash message to inform the user
                    $this->addFlash('error', 'Failed to upload the poster. Please try again.');

                    // Redirect back to the form.
                    return $this->redirectToRoute('add_movie');
                }

                // Set the poster field of the movie to the new filename
                $movie->setPoster($newFilename);


                // If the poster URL field is not empty, set the poster field to the URL
            } else {
                // Get the poster URL from the form
                $url = $form->get('posterUrl')->getData();
                // If the URL is not empty
                if (!empty($url)) {
                    // If the URL is a TMDB URL, extract the path
                    if (preg_match('/^https:\/\/image.tmdb.org\/t\/p\/w500\/(.+)$/', $url, $matches)) {
                        // Set the poster field to the path
                        $movie->setPoster('/' . $matches[1]);
                        // If the URL is not a TMDB URL, set the poster field to the URL
                    } else {
                        // Set the poster field to the URL
                        $movie->setPoster($url);
                    }
                }else{
                    // Set the poster to a path of a default image
                    $movie->setPoster('noImage.jpg');
                }

            }

            // Set the directors
            $movie->setDirectors($form->get('directors')->getData());
            // Set the actors
            $movie->setActors($form->get('actors')->getData());
            // Save the movie to the database
            $entityManager->persist($movie);
            // Flush the changes
            $entityManager->flush();
            // Log the movie creation
            $logger->info('New movie added', [
                'user' => $this->getUser()->getId(),
                'movie' => $movie->getTitle(),
            ]);

            // Set a success flash message. Its behavior is defined in templates/base.html.twig using a javascript function.
            $this->addFlash('success', 'Your movie has been added successfully.');

            // Redirect to the home page
            return $this->redirectToRoute('home');
        }

        // Render the form
        return $this->render('add_movie/index.html.twig', [
            // Pass the form to the template
            'form' => $form->createView(),
        ]);
    }


    // The route for searching for movies by title for suggestions
    #[Route('/search-movie-title', name: 'search_movie_title', methods: ['GET'])]
    public function searchMovieTitle(Request $request): JsonResponse
    {
        // Get the query parameter from the request
        $query = $request->query->get('query', '');

        // If the query is empty, return an empty JSON response
        if (empty($query)) {
            // Return an empty JSON response
            return $this->json([]);
        }

        // Use the TmdbService to search for movies
        $movies = $this->tmdbService->searchMovieTitlesForSuggestions($query);

        // Return the search results as JSON
        return $this->json($movies);
    }





    // The route for getting movie details by ID
    #[Route('/get-movie-details', name: 'get_movie_details', methods: ['GET'])]
    public function getMovieDetails(Request $request): JsonResponse
    {
        $movieId = $request->query->get('id', 0);

        if (empty($movieId)) {
            return $this->json([]);
        }

        $movieDetails = $this->tmdbService->fetchMovieDetailsById($movieId);

        return $this->json($movieDetails);
    }
}
