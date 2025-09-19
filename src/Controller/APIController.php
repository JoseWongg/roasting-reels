<?php

namespace App\Controller;

//use App\Entity\Announcement;
//use App\Form\AnnouncementAPIType;
//use App\Repository\AnnouncementRepository;
use App\Entity\Review;
use App\Form\MovieAPIType;
use App\Form\ReviewAPIType;
use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext; // Import the SerializationContext class. This class is used to configure the serialization context.
use JMS\Serializer\SerializerInterface; // Import the SerializerInterface class. This interface is used to serialize objects to JSON.
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Movie;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted; // Import the IsGranted annotation to restrict access to certain endpoints based on user roles.


// import the FOSRestBundle annotations
use FOS\RestBundle\Controller\Annotations as Rest;
// Import the OpenApi annotations
use OpenApi\Attributes as OA;
// Import the Nelmio annotations
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Service\Attribute\Required;


// This class represents the API controller for the application.
class APIController extends AbstractFOSRestController
{

    // Renders the developers page with the API documentation.
    #[Route('/docs', name: 'api_docs')]
    public function docs(): Response
    {
        return $this->render('api/index.html.twig');
    }


///////////////////////////////////   MOVIE ENDPOINTS   /////////////////////////////////////////////


    // Swagger(OpenAPI) documentation for the getMovies endpoint.
    #[OA\Get(
        path: '/api/v1/movies',
        operationId: 'getAllMovies',
        description: 'Retrieves a comprehensive list of movies, including details such as titles, IMDB IDs, overviews, poster images and running times. Does not include actors, and directors. Requires valid authentication. Responses are cacheable for 1 hour.',
        summary: 'This endpoint retrieves a comprehensive list of movies from the database. Does not include actors, and directors. Requires valid authentication.',
        security: [
            ['bearerAuth' => []]
        ],
        tags: ['Movies'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(
                            type: Movie::class,
                            groups: ['movie_list'])),
                    example: [
                        [
                            'id' => 1,
                            'title' => 'The Matrix',
                            'imdb_id' => '0133093',
                            'overview' => 'A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.',
                            'poster' => '/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg',
                            'running_time' => 136
                        ],
                        [
                            'id' => 2,
                            'title' => 'Inception',
                            'imdb_id' => '1375666',
                            'overview' => 'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.',
                            'poster' => '/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg',
                            'running_time' => 148
                        ]
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No movies found',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'No movies found'
                    ]
                )

            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized. Valid authentication credentials were not provided.',content: new OA\JsonContent(
                    example: [
                        'error' => 'Unauthorized access to this resource. Please provide valid credentials.'
                    ]
                )
            )
        ]
    )]
    // Function that responds to GET requests and returns all existing movies.
    #[Rest\Get('/api/v1/movies', name: 'get_movies')]
    public function getMovies(MovieRepository $movieRepository, SerializerInterface $serializer): Response {
        // Fetch all movies from the database
        $movies = $movieRepository->findAll();

        // If no movies are found, return a 404 response using FOSRestBundle View
        if (!$movies) {
            $view = View::create(['error' => 'No movies found'], Response::HTTP_NOT_FOUND);
            return $this->handleView($view);
        }
        // Use FOSRestBundle's View to handle serialization and response creation
        $view = View::create($movies);
        // Set the serialization groups to include only the properties needed for the movie list
        $view->getContext()->setGroups(['movie_list']);
        //Uses the handleView method of fOSRestBundle to return the response
        $response = $this->handleView($view);
        // Set the response as public to allow caching
        $response->setPublic();
        // Set the max-age to 3600 seconds (1 hour)
        $response->setMaxAge(3600);
        // Return the response using FOSRestBundle's View handler. The response contains the serialized movies.
        return $response;
    }



    // Swagger(OpenAPI) documentation for the getSingleMovie endpoint.
    #[OA\Get(
        path: '/api/v1/movies/{movieId}',
        operationId: 'getSingleMovie',
        description: 'This endpoint fetches detailed information about a movie by its unique identifier (ID). It returns data including the movie’s title, IMDB ID, overview, poster image, running time. does not include actors, and directors. Requires valid authentication. Responses are cacheable for 1 hour.',
        summary: 'Retrieves detailed information about a specific movie, not including its cast and directors.',
        security: [
            ['bearerAuth' => []]
        ],
        tags: ['Movies'],
        parameters: [
            new OA\Parameter(
                name: 'movieId',
                description: 'The ID of the movie to retrieve',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    ref: new Model(
                        type: Movie::class,
                        groups: ['movie_details']),
                    example: [
                        'id' => 1,
                        'title' => 'The Matrix',
                        'imdb_id' => '0133093',
                        'overview' => 'A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.',
                        'poster' => '/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg',
                        'running_time' => 136
                    ])
            ),
            new OA\Response(
                response: 404,
                description: 'Movie not found',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'Movie not found'
                    ])
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized. Valid authentication credentials were not provided.',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'Unauthorized access to this resource. Please provide valid credentials.'
                    ]
                )
            )
        ]
    )]
    // Function that responds to GET requests and returns details of a single movie by its ID (Doesn't include reviews or crew)
    #[Rest\Get('/api/v1/movies/{movieId}', name: 'get_movie')]
    public function getMovie(MovieRepository $movieRepository, SerializerInterface $serializer, int $movieId, LoggerInterface $logger): Response {
        // Fetch the movie from the database
        $movie = $movieRepository->find($movieId);

        // If no movie is found, return a 404 response using FOSRestBundle View
        if (!$movie) {
            // Create a View instance for the error response with a 404 status code and return it using the handleView method of the controller.
            $view = View::create(['error' => 'Movie not found'], Response::HTTP_NOT_FOUND);
            // Return the View instance
            return $this->handleView($view);
        }
        // Create a View instance for the successful response
        // Automatically handles serialization based on configuration
        $view = View::create($movie);
        // Set the serialization groups to include only the properties needed for the movie details
        $view->getContext()->setGroups(['movie_details']);
        //set cache control headers
        $response = $this->handleView($view);
        // Set the response as public to allow caching
        $response->setPublic();
        // Set the max-age to 3600 seconds (1 hour)
        $response->setMaxAge(3600);
        // return the response
        return $response;
    }



    // Swagger(OpenAPI) documentation for the getMovieCrew endpoint.
    #[OA\Get(
        path: '/api/v1/movies/{movieId}/crew',
        operationId: 'getMovieCrew',
        description: 'This endpoint fetches the crew of a movie by its unique identifier (ID). It returns data including the movie’s actors, and directors. Requires valid authentication. Responses are cacheable for 1 hour.',
        summary: 'Retrieves movie actors and directors.',
        security: [
            ['bearerAuth' => []]
        ],
        tags: ['Movies'],
        parameters: [
            new OA\Parameter(
                name: 'movieId',
                description: 'The ID of the movie to retrieve',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    ref: new Model(
                        type: Movie::class,
                        groups: ['movie_crew']),
                    example: [
                        'id' => 1,
                        'title' => 'The Matrix',
                        'actors' => ["Keanu Reeves", "Laurence Fishburne", "Carrie-Anne Moss"],
                        'directors' => ["Lana Wachowski", "Lilly Wachowski"]
                    ])
            ),
            new OA\Response(
                response: 404,
                description: 'Movie not found',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'Movie not found'
                    ])
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized. Valid authentication credentials were not provided.', content: new OA\JsonContent(
                    example: [
                        'error' => 'Unauthorized access to this resource. Please provide valid credentials.'
                    ]
                )
            )
        ]
    )]
    // Function that responds to GET requests and returns details of a single movie crew.
    #[Rest\Get('/api/v1/movies/{movieId}/crew', name: 'get_movie_crew')]
    public function getMovieCrew(MovieRepository $movieRepository, SerializerInterface $serializer, int $movieId, LoggerInterface $logger): Response {
        // Fetch the movie from the database
        $movie = $movieRepository->find($movieId);

        // If no movie is found, return a 404 response using FOSRestBundle View
        if (!$movie) {
            // Create a View instance for the error response with a 404 status code and return it using the handleView method of the controller.
            $view = View::create(['error' => 'Movie not found'], Response::HTTP_NOT_FOUND);
            // Return the View instance
            return $this->handleView($view);
        }
        // Create a View instance for the successful response
        // Automatically handles serialization based on configuration
        $view = View::create($movie);
        // Set the serialization groups to include only the properties needed for the movie details
        $view->getContext()->setGroups(['movie_crew']);
        // Return the View instance. The response contains the serialized movie.
        $response = $this->handleView($view);
        // Set the response as public to allow caching
        $response->setPublic();
        // Set the max-age to 3600 seconds (1 hour)
        $response->setMaxAge(3600);
        //return the response
        return $response;
    }



    // Swagger(OpenAPI) documentation for the createMovie endpoint.
    #[OA\Post(
        path: '/api/v1/movies',
        operationId: 'createMovie',
        description: 'This endpoint allows for the creation of a new movie entity within the database. It requires a JSON payload with details such as the movie’s title, IMDB ID, overview, poster URL, running time, actors, and directors. Successful creation of the movie will return the created movie data along with a Location header pointing to the newly created movie resource. Requires valid authentication. Responses are not cacheable.',
        summary: 'Creates a new movie with the provided details.',
        security: [
            ['bearerAuth' => []]
        ],
        requestBody: new OA\RequestBody(
            description: 'The details of the movie to create',
            required: true,
            content: new OA\JsonContent(
            required: ['title'],
                properties: [
                    new OA\Property(
                        property:
                        'title',
                        type: 'string',
                        example: 'The Matrix'),
                    new OA\Property(
                        property: 'imdb_id',
                        type: 'string',
                        example: '0133093'),
                    new OA\Property(
                        property: 'overview',
                        type: 'string',
                        example: 'A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.'),
                    new OA\Property(
                        property: 'poster',
                        type: 'string',
                        example: '/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg'),
                    new OA\Property(
                        property: 'running_time',
                        type: 'integer',
                        example: 136),
                    new OA\Property(
                        property: 'actors',
                        type: 'array',
                        items: new OA\Items(
                            type: 'string'),
                        example: ["Keanu Reeves", "Laurence Fishburne", "Carrie-Anne Moss"]),
                    new OA\Property(
                        property: 'directors',
                        type: 'array',
                        items: new OA\Items(
                            type: 'string'),
                        example: ["Lana Wachowski", "Lilly Wachowski"]),
                ],
                example: [
                    'title' => 'The Matrix',
                    'imdb_id' => '0133093',
                    'overview' => 'A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.',
                    'poster' => '/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg',
                    'running_time' => 136,
                    'actors' => ["Keanu Reeves", "Laurence Fishburne", "Carrie-Anne Moss"],
                    'directors' => ["Lana Wachowski", "Lilly Wachowski"]
                ]
            )
        ),
        tags: ['Movies'],
        responses: [

            new OA\Response(
                response: 201,
                description: 'Movie successfully created',
                headers: ['Location' => new OA\Header(
                    header: 'Location',
                    description: 'URL to the newly created movie',
                    schema: new OA\Schema(
                    type: 'string',
                    format: 'url'
                ))],
                content: new OA\JsonContent(
                    ref: new Model(
                        type: Movie::class,
                        groups: ['movie_details']))
            ),

            new OA\Response(
                response: 400,
                description: 'Invalid JSON format or validation error',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'Invalid JSON format'
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized. Valid authentication credentials were not provided.',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'Unauthorized access to this resource. Please provide valid credentials.'
                    ]
                )
            )
        ]
    )]
    // Function that responds to POST requests and creates a new movie
    #[Rest\Post(path: '/api/v1/movies', name: 'movie_create')]
    public function createMovie(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        // Decode JSON data
        $data = json_decode($request->getContent(), true);

        // Check if the JSON data is valid
        if ($data === null) {
            // Create and return a View instance for the error response with a 400 Bad Request status code
            $view = View::create(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
            // Return the View instance
            return $this->handleView($view);
        }

        // Instantiate a new Movie object
        $movie = new Movie();

        // Create the form and bind it to the Movie entity
        $form = $this->createForm(MovieAPIType::class, $movie);

        // Submit the form data
        $form->submit($data);

        // Validate the form
        if (!$form->isValid()) {
            // Create and return a View instance for the form errors with a 400 Bad Request status code
            $view = View::create($form->getErrors(), Response::HTTP_BAD_REQUEST);
            // Return the View instance
            return $this->handleView($view);
        }

        // Persist the movie entity
        $entityManager->persist($movie);
        // Flush the changes to the database
        $entityManager->flush();

        // Generate the URL for the newly created movie
        // No need to set the Location header explicitly since it's handled automatically by FOSRestBundle View class when using the generateUrl method.
        // No need to explicitly set the HTTP status code for 201 Created since it's the default
        // No need to manually serialize the movie object since it's handled automatically by FOSRestBundle View class
        // No need to get the ID of the newly created movie since it's already set in the entity object
        $location = $this->generateUrl('get_movie', ['movieId' => $movie->getId()]);

        // Create and return a View instance for the successful creation with a 201 Created status code
        $view = View::create($movie, Response::HTTP_CREATED);
        // Automatically handle serialization based on configuration
        $view->getContext()->setGroups(['movie_details']);
        // Set the Location header in the response
        $view->setHeader('Location', $location);
        // Add cache control headers to indicate that the response should not be cached
        $view->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
        // Return the View instance. The response contains the serialized movie and the Location header.
        return $this->handleView($view);
    }



    // Swagger(OpenAPI) documentation for the updateMovie endpoint.
    #[OA\Put(
        path: '/api/v1/movies/{movieId}',
        operationId: 'updateMovie',
        description: 'Allows clients to modify an existing movie’s information by specifying its ID and providing new values for fields such as title, IMDB ID, overview, poster URL, running time, actors, and directors. Allow partial update of fields. It requires authentication and returns the updated movie data, including a Location header pointing to the updated resource. Requires valid authentication and the user must have the ROLE_EDITOR role. Responses are not cacheable.',
        summary: 'Updates the specified movie with new details.',
        security: [
            ['bearerAuth' => []]
        ],
        requestBody: new OA\RequestBody(
            description: 'The updated details of the movie',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        example: 'The Matrix'),
                    new OA\Property(
                        property: 'imdb_id',
                        type: 'string',
                        example: '0133093'),
                    new OA\Property(
                        property: 'overview',
                        type: 'string',
                        example: 'A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.'),
                    new OA\Property(
                        property: 'poster',
                        type: 'string',
                        example: '/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg'),
                    new OA\Property(
                        property: 'running_time',
                        type: 'integer',
                        example: 136),
                    new OA\Property(
                        property: 'actors',
                        type: 'array',
                        items: new OA\Items(
                            type: 'string'),
                        example: ["Keanu Reeves", "Laurence Fishburne", "Carrie-Anne Moss"]),
                    new OA\Property(
                        property: 'directors',
                        type: 'array',
                        items: new OA\Items(
                            type: 'string'),
                        example: ["Lana Wachowski", "Lilly Wachowski"]),
                ],
                example: [
                    'title' => 'The Matrix',
                    'imdb_id' => '0133093',
                    'overview' => 'A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.',
                    'poster' => '/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg',
                    'running_time' => 136,
                    'actors' => ["Keanu Reeves", "Laurence Fishburne", "Carrie-Anne Moss"],
                    'directors' => ["Lana Wachowski", "Lilly Wachowski"]
                ]
            )
        ),
        tags: ['Movies'],
        parameters: [
            new OA\Parameter(
                name: 'movieId',
                description: 'The ID of the movie to update',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [

            new OA\Response(
                response: 200,
                description: 'Movie successfully updated',
                headers : ['Location' => new OA\Header(
                    header: 'Location',
                    description: 'URL to the updated movie',
                    schema: new OA\Schema(
                    type: 'string',
                    format: 'url'
                ))],
                content: new OA\JsonContent(

                    example: [
                        'id' => 1,
                        'title' => 'The Matrix',
                        'imdb_id' => '0133093',
                        'overview' => 'A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.',
                        'poster' => '/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg',
                        'running_time' => 136,
                        'actors' => ["Keanu Reeves", "Laurence Fishburne", "Carrie-Anne Moss"],
                        'directors' => ["Lana Wachowski", "Lilly Wachowski"]
                    ]
                )
            ),
            new OA\Response(
            response: 400,
            description: 'Invalid JSON format or validation error',
            content: new OA\JsonContent(
                example: [
                    'error' => 'Invalid JSON format'
                ])
            ),
            new OA\Response(
                response: 404,
                description: 'Movie not found',
                content: new OA\JsonContent(
                    example: [
                    'error' => 'Movie not found'
                ])
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized. Valid authentication credentials were not provided.',
                content: new OA\JsonContent(
                    example: [
                    'error' => 'Unauthorized access to this resource. Please provide valid credentials.'
                ])
            ),
            new OA\Response(
            response: 403,
            description: 'Forbidden. The user does not have permission (ROLE_EDITOR) to perform this action.',
            content: new OA\JsonContent(
                example: [
                'error' => 'You do not have permission to update this movie. ROLE_EDITOR required.'
            ])
),

        ]
    )]
    // Function that responds to PUT requests and updates a movie by its ID
    #[Rest\Put(path: '/api/v1/movies/{movieId}', name: 'movie_update')]
    // Access control using the IsGranted annotation to restrict access to ROLE_EDITOR users
    #[IsGranted('ROLE_EDITOR')]
    public function updateMovie(Request $request, EntityManagerInterface $entityManager, MovieRepository $movieRepository, int $movieId): Response {

        // Fetch the movie from the database
        $movie = $movieRepository->find($movieId);
        // Check if the movie exists
        if (!$movie) {
            // If the movie doesn't exist, return a 404 Not Found response
            return $this->handleView(View::create(['error' => 'Movie not found'], Response::HTTP_NOT_FOUND));
        }
        // Decode the JSON data from the request
        $data = json_decode($request->getContent(), true);
        // Check if the JSON data is valid
        if ($data === null) {
            // If the request body is not valid JSON, return a 400 Bad Request response
            return $this->handleView(View::create(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST));
        }
        // Create the form and bind it to the Movie entity
        $form = $this->createForm(MovieAPIType::class, $movie);
        // Submit the form data for partial updates
        $form->submit($data, false); // The second parameter 'false' is for partial updates. When set to false, the form will only update the fields that are present in the request body.

        // Validate the form
        if (!$form->isValid()) {
            // If the form submission is not valid, return a 400 Bad Request with form errors
            return $this->handleView(View::create($form->getErrors(), Response::HTTP_BAD_REQUEST));
        }
        // Persist the updated movie entity
        $entityManager->flush();


        // Generate the URL for the updated movie.
        // No need to set the Location header explicitly since it's handled automatically by FOSRestBundle View class when using the generateUrl method.
        // No need to manually serialize the movie object since it's handled automatically by FOSRestBundle View class
        // No need to explicitly set the HTTP status code for 200 OK since it's the default
        // No need to get the ID of the updated movie since it's already set in the entity object
        $location = $this->generateUrl('get_movie', ['movieId' => $movieId]);
        // Create a View instance for the successful update
        $view = View::create($movie); // Successfully updated movie

        $view->getContext()->setGroups(['movie_details']); // Set the serialization groups for the movie details

        // Automatically handle serialization based on configuration
        $view->setHeader('Location', $location);
        // Add cache control headers to indicate that the response should not be cached
        $view->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
        // Return the View instance. It contains the serialized movie and the Location header.
        return $this->handleView($view);
    }



    // Swagger(OpenAPI) documentation for the deleteMovie endpoint.
    #[OA\Delete(
        path: '/api/v1/movies/{movieId}',
        operationId: 'deleteMovie',
        description: 'This endpoint deletes a movie identified by its ID from the database. It requires authentication and returns no content upon successful deletion, but will return appropriate error messages if the movie is not found or if the request is unauthorized. Requires valid authentication and the user must have the ROLE_EDITOR role. Responses are not cacheable.',
        summary: 'Deletes a specific movie by its ID.',
        security: [
            ['bearerAuth' => []]
        ],
        tags: ['Movies'],
        parameters: [
            new OA\Parameter(
                name: 'movieId',
                description: 'The ID of the movie to delete',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Movie successfully deleted. No content in the response body.',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'Movie successfully deleted'
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Movie not found',content: new OA\JsonContent(
                    example: [
                        'error' => 'Movie not found'
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized. Valid authentication credentials were not provided.',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'Unauthorized access to this resource. Please provide valid credentials.'
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden. The user does not have permission (ROLE_EDITOR) to perform this action.',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'You do not have permission to delete this movie. ROLE_EDITOR required.'
                    ]
                )
            )
        ]
    )]
    // Function that responds to DELETE requests and deletes a movie by its ID
    #[Rest\Delete(path: '/api/v1/movies/{movieId}', name: 'delete_movie')]
    // Access control using the IsGranted annotation to restrict access to ROLE_EDITOR users
    #[IsGranted('ROLE_EDITOR')]
    public function deleteMovie(EntityManagerInterface $entityManager, MovieRepository $movieRepository, int $movieId): Response
    {
        // Fetch the movie from the database
        $movie = $movieRepository->find($movieId);

        // If no movie is found, return a 404 response using the View class for a standardized response structure
        if (!$movie) {
            // Return a 404 Not Found response
            return $this->handleView(View::create(['error' => 'Movie not found'], Response::HTTP_NOT_FOUND));
        }

        // Remove the movie entity from the database
        $entityManager->remove($movie);
        // Flush the changes to the database
        $entityManager->flush();

        // Create and return a View instance for the successful deletion with a 204 No Content status code
        $view = View::create(['message' => 'Movie successfully deleted'], Response::HTTP_NO_CONTENT);

        // Add cache control headers to indicate that the response should not be cached
        $view->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');

        // Return a 204 No Content response for successful deletion
        return $this->handleView($view);
    }






///////////////////////////////////   REVIEW ENDPOINTS (Subresource of Movie)   /////////////////////////////////////////////



    // Swagger(OpenAPI) documentation for the getMovieReviews endpoint.
    #[OA\Get(
        path: '/api/v1/movies/{movieId}/reviews',
        operationId: 'getMovieReviews',
        description: 'This endpoint fetches all the reviews associated with a given movie, identified by its ID. Each review includes details such as the reviewer’s information, review content, and rating. Authentication is required to access this endpoint. The response is an array of reviews if they are found; otherwise, an error message is returned indicating no reviews found or the movie does not exist. Requires valid authentication. Responses are cacheable for 1 hour.',
        summary: 'Retrieves all reviews for a specific movie.',
        security: [
            ['bearerAuth' => []]
        ],
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(
                name: 'movieId',
                description: 'The ID of the movie to retrieve reviews for',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer')

            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description:  'A list of reviews for the movie',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(
                            type: Review::class,
                            groups: ['review_list'])),
                    example: [
                        [
                            'id' => 1,
                            'reviewer' => 'John Doe',
                            'content' => 'This movie is amazing!',
                            'rating' => 5
                        ],
                        [
                            'id' => 2,
                            'reviewer' => 'Jane Smith',
                            'content' => 'I loved this movie!',
                            'rating' => 4
                        ]
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No reviews found for this movie',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'No reviews found for this movie'
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized. Valid authentication credentials were not provided.',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'Unauthorized access to this resource. Please provide valid credentials.'
                    ]
                )
            )
        ]
    )]
    // Function that responds to GET requests and returns all reviews associated with a movie
    #[Rest\Get('/api/v1/movies/{movieId}/reviews', name: 'get_movie_reviews')]
    public function getMovieReviews(MovieRepository $movieRepository, int $movieId): Response
    {
        // Fetch the movie by its ID
        $movie = $movieRepository->find($movieId);

        // If the movie is not found, return a 404 Not Found response
        if (!$movie) {
            //  Return a 404 Not Found response
            return $this->handleView(View::create(['error' => 'Movie not found'], Response::HTTP_NOT_FOUND));
        }

        // Retrieve all reviews associated with the movie
        $reviews = $movie->getReviews();

        // If no reviews are found, you might want to return an empty array or a specific message
        if ($reviews->isEmpty()) {
            // Return a 200 OK response with a message
            return $this->handleView(View::create(['message' => 'No reviews found for this movie'], Response::HTTP_OK));
        }

        // Return the reviews with a 200 OK response
        $view = View::create($reviews);
        // Set the serialization groups to include only the properties needed for the review list
        $view->getContext()->setGroups(['review_list']);
        //set cache control headers
        $response = $this->handleView($view);
        // Set the response as public to allow caching
        $response->setPublic();
        // Set the max-age to 3600 seconds (1 hour)
        $response->setMaxAge(3600);
        // return the response
        return $response;
    }



    // Swagger(OpenAPI) documentation for the getReview endpoint.
    #[OA\Get(
        path: '/api/v1/movies/{movieId}/reviews/{reviewId}',
        operationId: 'getReview',
        description: 'Retrieves the details of a single review for a given movie by the movie ID and review ID. It checks if both the movie and the review exist. If the movie is not found, a 404 Not Found response is returned. If the review is not found for the movie, a 404 Not Found response is returned specifically for the review. The response includes the review details such as the reviewer’s information, review content, and rating. Requires valid authentication. Responses are cacheable for 1 hour.',
        summary: 'Get a specific review for a movie',
        security: [
            ['bearerAuth' => []]
        ],
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(
                name: 'movieId',
                description: 'The ID of the movie',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer')
            ),
            new OA\Parameter(
                name: 'reviewId',
                description: 'The ID of the review',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Review found',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        ref: '#/components/schemas/Review'),
                    example: [
                        'id' => 1,
                        'reviewer' => 'John Doe',
                        'content' => 'This movie is amazing!',
                        'rating' => 5
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Not Found. Could be due to an invalid movie ID or review ID not associated with the given movie. Check the error message for more details.',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: 'error',
                                type: 'string',
                                example: 'Review not found for this movie'
                            )
                        ],
                        type: 'object'
                    ),
                    example: [
                        'error' => 'Review not found for this movie'
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized. Valid authentication credentials were not provided.',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'Unauthorized access to this resource. Please provide valid credentials.'
                    ]
                )
            )
        ]
    )]
    // Function that responds to GET requests and returns a single review by its ID
    #[Rest\Get('/api/v1/movies/{movieId}/reviews/{reviewId}', name: 'get_review')]
    public function getReview(MovieRepository $movieRepository, ReviewRepository $reviewRepository, int $movieId, int $reviewId): Response
    {
        // Fetch the movie by its ID
        $movie = $movieRepository->find($movieId);
        // Check if the movie exists
        if (!$movie) {
            // If the movie doesn't exist, return a 404 Not Found response.
            $view = View::create(['error' => 'Movie not found'], Response::HTTP_NOT_FOUND);
            // Return the View instance
            return $this->handleView($view);
        }

        // Check if the review exists
        $review = $reviewRepository->findOneBy([
            'id' => $reviewId,
            'movie' => $movieId
        ]);

        // If the review is not found, return a 404 Not Found response specifically for the review
        if (!$review) {
            $view = View::create(['error' => 'Review not found for this movie'], Response::HTTP_NOT_FOUND);
            // Return the View instance
            return $this->handleView($view);
        }

        // Create a View instance for the successful response
        $view = View::create($review);
        // Set the serialization groups to include only the properties needed for the review details
        $view->getContext()->setGroups(['review_detail']);
        // Return the View instance. The response contains the serialized review.
        $response = $this->handleView($view);
        // Set the response as public to allow caching
        $response->setPublic();
        // Set the max-age to 3600 seconds (1 hour)
        $response->setMaxAge(3600);
        // return the response
        return $response;
    }



    // Swagger(OpenAPI) documentation for the createReviewForMovie endpoint.
    #[OA\Post(
        path: '/api/v1/movies/{movieId}/reviews',
        operationId: 'createReviewForMovie',
        description: 'Submits a review for a movie specified by its ID. The score must be an integer between 1 and 5. The review title and text are required fields. Upon successful creation, the response includes the review details and a Location header pointing to the newly created review resource. Requires valid authentication. Responses are not cacheable.',
        summary: 'Create a review for a specific movie.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'reviewTitle',
                        type: 'string',
                        example: 'Amazing Movie'),
                    new OA\Property(
                        property: 'reviewText',
                        type: 'string',
                        example: 'Absolutely loved the plot twists!'),
                    new OA\Property(
                        property: 'score',
                        type: 'integer',
                        example: 4),
                ],
                example: [
                    'reviewTitle' => 'Amazing Movie',
                    'reviewText' => 'Absolutely loved the plot twists!',
                    'score' => 4,
                ]
            )
        ),
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(
                name: 'movieId',
                description: 'The ID of the movie for which the review is being submitted',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Review successfully created',
                headers: [new OA\Header(
                    header: 'Location',
                    description: 'URL to the newly created review',
                    schema: new OA\Schema(
                        type: 'string',
                        format: 'url'))],
                content: new OA\JsonContent(
                    required: ['review_title', 'review_text', 'score'],
                    properties: [
                        new OA\Property(
                            property: 'id',
                            description: 'The unique identifier for the review',
                            type: 'integer',
                            format: 'int64',
                            example: 37),
                        new OA\Property(
                            property: 'user',
                            description: 'The user who submitted the review',
                            properties: [
                            new OA\Property(
                                property: 'name',
                                type: 'string',
                                example: 'Bob Smith'),
                        ],
                            type: 'object'),
                        new OA\Property(
                            property: 'movie',
                            description: 'The movie for which the review was submitted',
                            properties: [
                            new OA\Property(
                                property: 'id',
                                type: 'integer',
                                format: 'int64',
                                example: 73),
                            new OA\Property(
                                property: 'title',
                                description: 'The title of the movie',
                                type: 'string',
                                format: 'string',
                                example: 'The Matrix Reloaded'),
                        ],
                            type: 'object', format: 'int64'),
                        new OA\Property(
                            property: 'review_title',
                            type: 'string',
                            example: 'Excellent!'),
                        new OA\Property(
                            property: 'review_text',
                            type: 'string',
                            example: 'I would recommend.'),
                        new OA\Property(
                            property: 'score',
                            type: 'integer',
                            example: 5),
                        new OA\Property(
                            property: 'date',
                            type: 'string',
                            format: 'date-time',
                            example: '2024-03-31T22:16:52+00:00'),
                    ],
                    type: 'object',
                    example: [
                        'id' => 37,
                        'user' => ['name' => 'Bob Smith'],
                        'movie' => [
                            'id' => 73,
                            'title' => 'The Matrix Reloaded'
                        ],
                        'review_title' => 'Excellent!',
                        'review_text' => 'I would recommend.',
                        'score' => 5,
                        'date' => '2024-03-31T22:16:52+00:00'
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input or validation failed',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'Invalid input'
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized. Valid authentication credentials were not provided.',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'Unauthorized access to this resource. Please provide valid credentials.'
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Movie not found',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'Movie not found'
                    ]
                )
            )
        ]
    )]
    // Function that responds to POST requests and creates a new review for a movie
    #[Rest\Post('/api/v1/movies/{movieId}/reviews', name: 'create_review_for_movie')]
    public function createReviewForMovie(Request $request, MovieRepository $movieRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, int $movieId): Response {
        // Fetch the movie by its ID
        $movie = $movieRepository->find($movieId);
        // Check if the movie exists
        if (!$movie) {
            // If the movie doesn't exist, return a 404 Not Found response
            return $this->handleView(View::create(['error' => 'Movie not found'], Response::HTTP_NOT_FOUND));
        }

        // Fetch the currently authenticated user
        $user = $this->getUser();
        // Check if the user is logged in
        if (!$user) {
            // If the user is not logged in, return a 401 Unauthorized response
            return $this->handleView(View::create(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED));
        }

        // Create a new Review instance
        $review = new Review();
        // Set the user, movie, and date for the review
        $review->setUser($user);
        // Set the movie for the review
        $review->setMovie($movie);
        // Set the date for the review
        $review->setDate(new \DateTime());

        // Create the form and bind it to the Review entity
        $form = $this->createForm(ReviewAPIType::class, $review);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        // Validate the form
        if ($form->isSubmitted() && !$form->isValid()) {
            // Create an array of errors
            $errors = [];
            // Loop through the form errors and add them to the array
            foreach ($form->getErrors(true) as $error) {
                // Get the property path and error message
                $propertyPath = $error->getOrigin()->getName();
                // Add the error message to the array
                $errors[$propertyPath] = $error->getMessage();
            }

            // Create a response with the array of errors
            $view = View::create([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Validation Failed',
                'errors' => $errors,
            ], Response::HTTP_BAD_REQUEST);

            // Return the response
            return $this->handleView($view);
        }

        // Persist the review entity
        $entityManager->persist($review);
        // Flush the changes to the database
        $entityManager->flush();

        // Generate the URL for the newly created review
        $location = $this->generateUrl('get_review', ['movieId' => $movieId, 'reviewId' => $review->getId()]);
        // Create a View instance for the successful creation
        $view = View::create($review, Response::HTTP_CREATED);
        // Set the serialization groups for the review details
        $view->getContext()->setGroups(['review_detail']);
        // Set the Location header in the response
        $view->setHeader('Location', $location);
        // Add cache control headers to indicate that the response should not be cached
        $view->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
        // Return the View instance. The response contains the serialized review and the Location header.
        return $this->handleView($view);
    }



    // Swagger (OpenAPI) documentation for the updateReview endpoint.
    #[OA\Put(
        path: '/api/v1/movies/{movieId}/reviews/{reviewId}',
        operationId: 'updateReview',
        description: 'Allows authenticated users to update their own review of a specified movie by providing new review details such as the review title, text, and score. Requires authentication and the user must have the ROLE_EDITOR role. The response includes the updated review data and a Location header pointing to the updated resource. Responses are not cacheable.',
        summary: 'Updates a specific review for a movie.',
        security: [
            ['bearerAuth' => []]
        ],
        requestBody: new OA\RequestBody(
            description: 'JSON payload containing the updated review details',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'reviewTitle',
                        description: 'The title of the review',
                        type: 'string',
                        example: 'Amazing Sequel'),
                    new OA\Property(
                        property: 'reviewText',
                        description: 'The text content of the review',
                        type: 'string',
                        example: 'The sequel surpassed my expectations with stunning visuals and a captivating plot.'),
                    new OA\Property(
                        property: 'score',
                        description: 'The score must be between 1 and 5.',
                        type: 'integer',
                        example: 5)
                ],
                example: [
                    'reviewTitle' => 'Amazing Sequel',
                    'reviewText' => 'The sequel surpassed my expectations with stunning visuals and a captivating plot.',
                    'score' => 5
                ]
            )
        ),
        tags: ['Reviews'],

        parameters: [
            new OA\Parameter(
                name: 'movieId',
                description: 'The ID of the movie associated with the review',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'reviewId',
                description: 'The ID of the review to update',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Review successfully updated',
                headers: ['Location' => new OA\Header(
                    header: 'Location',
                    description: 'URL to the updated review',
                    schema: new OA\Schema(
                        type: 'string',
                        format: 'url'))],
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'id',
                            type: 'integer',
                            example: 34),
                        new OA\Property(
                            property: 'user',
                            properties: [
                            new OA\Property(
                                property: 'name',
                                type: 'string',
                                example: 'Alice Smith')
                        ], type: 'object'),
                        new OA\Property(
                            property: 'movie',
                            properties: [
                            new OA\Property(
                                property: 'id',
                                type: 'integer',
                                example: 73),
                            new OA\Property(
                                property: 'title',
                                type: 'string',
                                example: 'The Matrix Reloaded')
                        ], type: 'object'),
                        new OA\Property(
                            property: 'review_title',
                            type: 'string',
                            example: 'Much better! The sequel is a must-watch.'),
                        new OA\Property(
                            property: 'review_text',
                            type: 'string',
                            example: 'Much better experience on second watch.'),
                        new OA\Property(
                            property: 'score',
                            type: 'integer',
                            example: 5),
                        new OA\Property(
                            property: 'date',
                            type: 'string',
                            format: 'date-time',
                            example: '2024-03-30T00:00:00+00:00')
                    ],
                    example: [
                        'id' => 34,
                        'user' => ['name' => 'Alice Smith'],
                        'movie' => [
                            'id' => 73,
                            'title' => 'The Matrix Reloaded'
                        ],
                        'review_title' => 'Much better! The sequel is a must-watch.',
                        'review_text' => 'Much better experience on second watch.',
                        'score' => 5,
                        'date' => '2024-03-30T00:00:00+00:00'
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input or validation failed'
            ),
            new OA\Response(
                response: 404,
                description: 'Not Found. Could be due to an invalid movie ID or review ID not associated with the given movie. Check the error message for more details.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Review not found for this movie')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized. Valid authentication credentials were not provided.',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'Unauthorized access to this resource. Please provide valid credentials.'
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden. The user does not have permission (ROLE_EDITOR) to perform this action.',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'You do not have permission to update this review. ROLE_EDITOR required.'
                    ]
                )
            )
        ]
    )]
    // Function that responds to PUT requests and updates a review by its ID
    #[Rest\Put('/api/v1/movies/{movieId}/reviews/{reviewId}', name: 'update_review')]
    // Access control using the IsGranted annotation to restrict access to ROLE_USER users
    #[IsGranted('ROLE_EDITOR')]
    public function updateReview(Request $request, EntityManagerInterface $entityManager, MovieRepository $movieRepository,ReviewRepository $reviewRepository, int $movieId, int $reviewId): Response {
        // Check if the movie exists
        $movie = $movieRepository->find($movieId);
        if (!$movie) {
            // If the movie doesn't exist, return a 404 Not Found response
            return $this->handleView(View::create(['error' => 'Movie not found'], Response::HTTP_NOT_FOUND));
        }

        // Fetch the review by its ID and the movie ID
        $review = $reviewRepository->findOneBy(['id' => $reviewId, 'movie' => $movieId]);
        if (!$review) {
            // If the review doesn't exist for this movie, return a 404 Not Found response
            return $this->handleView(View::create(['error' => 'Review not found for this movie'], Response::HTTP_NOT_FOUND));
        }

        // Decode the JSON data from the request
        $data = json_decode($request->getContent(), true);
        // Create the form and bind it to the Review entity
        $form = $this->createForm(ReviewAPIType::class, $review);
        // Submit the form data for partial updates
        $form->submit($data, false);
        // Validate the form
        if ($form->isSubmitted() && !$form->isValid()) {
            // Create an array of errors
            $errors = [];
            // Loop through the form errors and add them to the array. This is useful for returning multiple errors in the response including score validation.
            foreach ($form->getErrors(true) as $error) {
                // Get the property path and error message
                $propertyPath = $error->getOrigin()->getName();
                // Add the error message to the array
                $errors[$propertyPath] = $error->getMessage();
            }
            // Create a response with the array of errors
            return $this->handleView(View::create([
                // Return the array of errors
                'code' => Response::HTTP_BAD_REQUEST,
                // Return a message indicating that validation failed
                'message' => 'Validation Failed',
                // Return the array of errors
                'errors' => $errors,
                // Return a 400 Bad Request status code
            ], Response::HTTP_BAD_REQUEST));
        }
        // Persist the updated review entity
        $entityManager->flush();
        // Generate the URL for the updated review
        $location = $this->generateUrl('get_review', ['movieId' => $movieId, 'reviewId' => $reviewId]);
        // Create a View instance for the successful update
        $view = View::create($review);
        // Set the serialization groups for the review details
        $view->getContext()->setGroups(['review_detail']);
        // Automatically handle serialization based on configuration
        $view->setHeader('Location', $location);
        // Add cache control headers to indicate that the response should not be cached
        $view->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
        // Return the View instance. It contains the serialized review and the Location header.
        return $this->handleView($view);
    }


    // Swagger(OpenApi) attribute for the documentation of the deleteReview endpoint.
    #[OA\Delete(
        path: '/api/v1/movies/{movieId}/reviews/{reviewId}',
        operationId: 'deleteReview',
        description: 'Deletes a review for a specific movie by the review and movie IDs. Returns no content upon successful deletion. It returns a 404 Not Found response if either the movie or the review does not exist. Requires valid authentication and the user must have the ROLE_USER role. Responses are not cacheable.',
        summary: 'Deletes a specific review for a movie.',
        security: [['bearerAuth' => []]],
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(
                name: 'movieId',
                description: 'The ID of the movie',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer')
            ),
            new OA\Parameter(
                name: 'reviewId',
                description: 'The ID of the review to delete',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Review successfully deleted. No content in the response body.'
            ),
            new OA\Response(
                response: 404,
                description: 'Not Found. Could be due to an invalid movie ID or review ID not associated with the given movie. Check the error message for more details.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Review not found for this movie')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized. Valid authentication credentials were not provided.',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'Unauthorized access to this resource. Please provide valid credentials.'
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden. The user does not have permission (ROLE_EDITOR) to perform this action.',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'You do not have permission to delete this review. ROLE_EDITOR required.'
                    ]
                )
            )
        ]
    )]
    // Function that responds to DELETE requests and deletes a review by its ID
    #[Rest\Delete('/api/v1/movies/{movieId}/reviews/{reviewId}', name: 'delete_review')]
    // Access control using the IsGranted annotation to restrict access to ROLE_USER users
    #[IsGranted('ROLE_EDITOR')]
    public function deleteReview(EntityManagerInterface $entityManager, MovieRepository $movieRepository, ReviewRepository $reviewRepository, int $movieId, int $reviewId): Response {
        // Check if the movie exists
        $movie = $movieRepository->find($movieId);
        if (!$movie) {
            // If the movie doesn't exist, return a 404 Not Found.
            return $this->handleView(View::create(['error' => 'Movie not found'], Response::HTTP_NOT_FOUND));
        }

        // Check if the review exists for the movie
        $review = $reviewRepository->findOneBy(['id' => $reviewId, 'movie' => $movieId]);
        if (!$review) {
            // If the review doesn't exist for the found movie, return a 404 Not Found response specifically for the review
            return $this->handleView(View::create(['error' => 'Review not found for this movie'], Response::HTTP_NOT_FOUND));
        }

        // Remove the review entity from the database
        $entityManager->remove($review);
        // Flush the changes to the database
        $entityManager->flush();

        // Create a View instance for the successful deletion with a 204 No Content status code
        $view = View::create(['message' => 'Review successfully deleted'], Response::HTTP_NO_CONTENT);
        // Add cache control headers to indicate that the response should not be cached
        $view->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
        // Return a 204 No Content response for successful deletion
        return $this->handleView($view);

    }
}
