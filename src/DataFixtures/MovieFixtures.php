<?php
namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use GuzzleHttp\Client;
use App\Entity\Movie;

/**
 * Class MovieFixtures
 * @package App\DataFixtures
 * This class is used to load movie data from the Movie Database API
 * The execution of this class is triggered by the command: php bin/console doctrine:fixtures:load
 * It will only load the first page of "popular" movies from the API
 * To implement pagination, you can use the "page" query parameter in the API request
 * See https://www.themoviedb.org/talk/5bce078d9251410574000bfb for more details
 */
class MovieFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $client = new Client([
            'base_uri' => 'https://api.themoviedb.org',
            'headers' => [
                // PENDING: This should be referenced from .env
                'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJkNGVmYmQ1ODAxZDRmNGIyZjM4OTllNjc1YmEyMWI1YyIsInN1YiI6IjY1NjQ3NmVmOGYyNmJjMDBhZDI3OGJjMyIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.DPYrYnWK8jPl61PTaKfcsAFWlKx4sIpeniNWcXmTxjY',
                'accept' => 'application/json',
            ],
        ]);

        $response = $client->request('GET', '/3/movie/popular');
        $data = json_decode($response->getBody(), true);

        foreach ($data['results'] as $movieData) {
            $movie = new Movie();
            $movie->setTitle($movieData['title']);
            $movie->setImdbId($movieData['id']);
            $movie->setOverview($movieData['overview']);
            $movie->setPoster($movieData['poster_path']);

            // Fetch additional movie details for runtime
            $movieDetailsResponse = $client->request('GET', "/3/movie/{$movieData['id']}");
            $movieDetails = json_decode($movieDetailsResponse->getBody(), true);
            $movie->setRunningTime($movieDetails['runtime'] ?? null);

            // Fetch movie credits
            $creditsResponse = $client->request('GET', "/3/movie/{$movieData['id']}/credits");
            $credits = json_decode($creditsResponse->getBody(), true);

            // Extract actors and director
            $actors = [];
            $directors = [];
            foreach ($credits['cast'] as $castMember) {
                if (isset($castMember['character'])) {
                    $actors[] = $castMember['name'];
                }
            }
            foreach ($credits['crew'] as $crewMember) {
                if (isset($crewMember['job']) && $crewMember['job'] === 'Director') {
                    $directors[] = $crewMember['name'];
                }
            }

            // Set actors and directors
            $movie->setActors($actors);
            $movie->setDirectors($directors);

            $manager->persist($movie);
        }

        $manager->flush();
    }
}