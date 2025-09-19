<?php

namespace App\Services;
use GuzzleHttp\Client;
use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This service is used to fetch movies from the TMDB API and store them in the database.
 * It makes use of the Guzzle HTTP client to make requests to the TMDB API.
 */
class TmdbService
{
    // The Guzzle HTTP client
    private Client $client;
    // The Doctrine entity manager
    private EntityManagerInterface $entityManager;
    // The logger
    private $logger;

    // Constructor to inject the entity manager and logger
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        // Initialize the Guzzle HTTP client
        $this->client = new Client([
            // Verify SSL certificate
            //'verify' => __DIR__ . '/../../resources/cacert.pem',
            'verify' => $_ENV['CACERT_PATH'],
            'base_uri' => 'https://api.themoviedb.org',
            // Set the default timeout to 5 seconds
            'timeout'  => 5.0,
        ]);
        // Inject the entity manager
        $this->entityManager = $entityManager;
        // Inject the logger
        $this->logger = $logger;
    }



    /**
     * Searches for movie titles based on the query provided.
     *
     * @param string $title The movie title to search for.
     *
     * @return array An array of movie titles and ids.
     */
    public function searchMovieTitlesForSuggestions(string $title): array {
        // Bearer token for authorization
        $bearerToken = $_ENV['TMDB_API_BEARER_TOKEN'];
        try {
            // Search for movies by the title provided in the query
            $response = $this->client->request('GET', '/3/search/movie', [
                // Query parameters for the search request
                'query' => [
                    'query' => $title,
                    'include_adult' => 'false',
                    'language' => 'en-US',
                ],
                // Headers for the request
                'headers' => [
                    'Authorization' => 'Bearer ' . $bearerToken,
                    'Accept' => 'application/json',
                ],
            ]);

            // Decode the JSON response
            $data = json_decode($response->getBody(), true);

            // Initialize an array to hold the movie titles and ids
            $suggestions = [];
            // Loop through the search results and extract the movie titles and ids
            foreach ($data['results'] as $movie) {
                // Add the movie title and id to the suggestions array
                $suggestions[] = [
                    'id' => $movie['id'],
                    'title' => $movie['title'],
                ];
            }

            // Return the array of suggestions
            return $suggestions;

            // Catch any exceptions that occur during the request
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // Log the error and return an empty array or error message
            $this->logger->error('Error searching for movie titles: ' . $e->getMessage());

            // Return an error message
            return ['error' => 'An error occurred while searching for movies.'];
        }
    }





    /**
     * Fetches detailed information about a movie by its ID.
     *
     * @param int $movieId The ID of the movie to fetch details for.
     *
     * @return array An array of detailed movie data.
     */
    public function fetchMovieDetailsById(int $movieId): array
    {
        // Bearer token for authorization
        $bearerToken = $_ENV['TMDB_API_BEARER_TOKEN'];
        try {
            // Fetch the main movie details
            $movieDetailsResponse = $this->client->request('GET', "/3/movie/{$movieId}", [
                'query' => ['language' => 'en-US'],
                'headers' => [
                    'Authorization' => 'Bearer ' . $bearerToken,
                    'Accept' => 'application/json',
                ],
            ]);
            // Decode the response JSON data
            $movieDetails = json_decode($movieDetailsResponse->getBody(), true);

            // Fetch movie credits for actors and directors
            $creditsResponse = $this->client->request('GET', "https://api.themoviedb.org/3/movie/{$movieId}/credits", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $bearerToken,
                    'Accept' => 'application/json',
                ],
            ]);
            // Decode the response JSON data
            $credits = json_decode($creditsResponse->getBody(), true);

            // Initialize arrays for actors and directors
            $actors = [];
            $directors = [];

            // Iterate over cast to include only those with a character name
            foreach ($credits['cast'] as $castMember) {
                // Check if the cast member has a character name
                if (!empty($castMember['character'])) {
                    // Add the actor's name to the actors array
                    $actors[] = $castMember['name'];
                }
            }

            // Iterate over crew to include only those with a job of Director
            foreach ($credits['crew'] as $crewMember) {
                // Check if the crew member has a job title of Director
                if ($crewMember['job'] === 'Director') {
                    // Add the director's name to the directors array
                    $directors[] = $crewMember['name'];
                }
            }

            // Compile the detailed movie data
            $movie = [
                'id' => $movieId,
                'title' => $movieDetails['title'],
                'overview' => $movieDetails['overview'],
                'poster_path' => 'https://image.tmdb.org/t/p/w500' . $movieDetails['poster_path'],
                'running_time' => $movieDetails['runtime'] ?? 'N/A',
                'actors' => $actors,
                'directors' => $directors,
            ];

            // Return the detailed movie data
            return $movie;
            // Catch any exceptions that occur during the request
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // Log the error and return an empty array or error message
            $this->logger->error("Failed to fetch details for movie ID {$movieId}: " . $e->getMessage());

            // Return an error message
            return ['error' => 'An error occurred while fetching movie details.'];
        }
    }



    // Fetches the YouTube trailer ID for a movie by its ID.
    public function fetchYoutubeTrailerId(string $movieId): ?string
    {
        // Bearer token for authorization
        $bearerToken = $_ENV['TMDB_API_BEARER_TOKEN'];
        try {
            // Fetch videos for the movie
            $response = $this->client->request('GET', "/3/movie/{$movieId}/videos", [
                'query' => ['language' => 'en-US'],
                'headers' => [
                    'Authorization' => 'Bearer ' . $bearerToken,
                    'Accept' => 'application/json',
                ],
            ]);

            // Decode the JSON response
            $data = json_decode($response->getBody(), true);

            // Loop through the videos to find the first YouTube video
            foreach ($data['results'] as $video) {
                // Check if the video is from YouTube and is a trailer
                if ($video['site'] === 'YouTube' && $video['type'] === 'Trailer') {
                    // Return the YouTube video ID (key) of the first matching video
                    return $video['key'];
                }
            }

            // If no YouTube videos are found, return null
            return null;
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // Log the error
            $this->logger->error('Failed to fetch YouTube trailer ID for movie ID ' . $movieId . ': ' . $e->getMessage());

            // Return null in case of error
            return null;
        }
    }



    /**
     * Fetches movies from the TMDB API and stores them in the database.
     *
     * @return string
     *
     * Use with caution: This method fetches a large number of movies from the TMDB API and stores them in the database.
     *
     * Execute using the command: php bin/console app:populate-movies (see /src/Command/PopulateMoviesCommand.php)
     */
    public function fetchAndStoreMovies()
    {
        // Bearer token for authorization
        $bearerToken = $_ENV['TMDB_API_BEARER_TOKEN'];
        // Use a try-catch block to handle any exceptions that may occur during the request
        try {
            // Make a GET request to the TMDB API to fetch popular movies
            $response = $this->client->request('GET', '/3/movie/popular', [
                // Query parameters for the request
                'headers' => [
                    'Authorization' => 'Bearer ' . $bearerToken,
                    'accept' => 'application/json',
                ],
            ]);

            // fetch the response from the TMDB API
            $data = json_decode($response->getBody(), true);

            // Loop through the results and store each movie in the database
            foreach ($data['results'] as $movieData) {
                $movie = new Movie();
                $movie->setTitle($movieData['title']);
                $movie->setImdbId($movieData['id']);
                $movie->setOverview($movieData['overview']);
                $movie->setPoster($movieData['poster_path']);


                // Fetch additional movie details for runtime
                $movieDetailsResponse = $this->client->request('GET', "/3/movie/{$movieData['id']}", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $bearerToken,
                        'accept' => 'application/json',
                    ],
                ]);
                // Decode the response JSON data
                $movieDetails = json_decode($movieDetailsResponse->getBody(), true);

                // Set the running time for the movie
                $this->logger->info('Fetched details for movie: ' . $movieData['title']);

                // Set the running time for the movie
                $movie->setRunningTime($movieDetails['runtime'] ?? null);


                // Fetch movie credits
                $creditsResponse = $this->client->request('GET', "/3/movie/{$movieData['id']}/credits", [
                    'headers' => [
                        //'Authorization' => 'Bearer ' . $bearerToken,
                        'accept' => 'application/json',
                    ],
                ]);
                // Decode the response JSON data
                $credits = json_decode($creditsResponse->getBody(), true);

                // Extract actors and director
                $actors = [];
                $directors = [];
                // Iterate over cast to include only those with a character name
                foreach ($credits['cast'] as $castMember) {
                    // Check if the cast member has a character name
                    if (isset($castMember['character'])) {
                        // Add the actor's name to the actors array
                        $actors[] = $castMember['name'];
                    }
                }
                // Iterate over crew to include only those with a job title of Director
                foreach ($credits['crew'] as $crewMember) {
                    // Check if the crew member has a job title of Director
                    if (isset($crewMember['job']) && $crewMember['job'] === 'Director') {
                        // Add the director's name to the directors array
                        $directors[] = $crewMember['name'];
                    }
                }

                // Set actors and directors
                $movie->setActors($actors);
                // Set directors
                $movie->setDirectors($directors);

                // Persist the movie
                $this->entityManager->persist($movie);
            }

            // Flush to save the movies to the database
            $this->entityManager->flush();

            // Log the successful fetch and store operation
            $this->logger->info('Fetched and stored movies from TMDB API.');
            // Return a success message
            return 'Movies successfully fetched and stored.';

            // Catch any exceptions that occur during the request
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // Log the error and return an error message
            return 'Request failed: ' . $e->getMessage();
        }
    }






    //Uncomment this method to manually insert a movie into the database for testing purposes.
    /**
    public function testInsertMovie()
    {
    // Create a new Movie object
    $movie = new Movie();
    $movie->setTitle('Test Movie');
    $movie->setImdbId('tt1234567');
    $movie->setOverview('This is a test movie.');
    $movie->setPoster('test_poster.jpg');
    $movie->setRunningTime(120);
    $movie->setActors(['Actor 1', 'Actor 2']);
    $movie->setDirectors(['Director 1']);

    // Persist the movie
    $this->entityManager->persist($movie);

    // Flush to save the movie to the database
    $this->entityManager->flush();
    }
     */
}
