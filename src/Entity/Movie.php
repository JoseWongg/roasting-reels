<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use JMS\Serializer\Annotation\Groups; // import the Groups class

/**
 * @ORM\Entity(repositoryClass=MovieRepository::class)
 * This class represents a movie in the database.
 */
#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[ORM\Table(name: "movies")]//Set the table name to "movies" instead of "movie"
#[UniqueEntity(fields: ['title'], message: 'This title already exist!')] // set the title to be unique
#[ExclusionPolicy("all")] // Sets the overall exclusion policy with a default serialization behavior to exclude all properties
class Movie
{
    /**
     * @var int|null The id of the movie.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Expose] // Exposes the property to serialization
    #[Groups(['movie_list', 'movie_details', 'review_detail', 'movie_crew'])] // Add the property to these groups: "movie_list", "movie_details", "review_detail" and "movie_crew"
    private ?int $id= null;

    /**
     * @var string|null The title of the movie.
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: "The title must not be null.")]
    #[Expose] // Exposes the property to serialization
    #[Groups(['movie_list', 'movie_details', 'review_detail', 'movie_crew'])]
    private ?string $title = null;

    /**
     * @var string|null The IMDb ID of the movie.
     */
    //#[ORM\Column(length: 255, unique: true)]
    #[ORM\Column(length: 255, nullable: true)]
    #[Expose]
    #[Groups(['movie_details'])]
    private ?string $imdbId = null;

    /**
     * @var string|null The overview of the movie.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Expose]
    #[Groups(['movie_details'])]
    private ?string $overview = null;

    /**
     * @var string|null The poster of the movie.
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Expose]
    #[Groups(['movie_details'])]
    private ?string $poster = null;

    /**
     * @var int|null The running time of the movie in minutes.
     */
    #[ORM\Column(nullable: true)]
    #[Expose]
    #[Groups(['movie_details'])]
    private ?int $runningTime = null;

    /**
     * @var array|null The actors in the movie.
     */
    #[ORM\Column(nullable: true)]
    #[Expose]
    #[Groups(['movie_crew'])]
    private ?array $actors = null;

    /**
     * @var array|null The directors of the movie.
     */
    #[ORM\Column(nullable: true)]
    #[Expose]
    #[Groups(['movie_crew'])]
    private ?array $directors = null;

    /**
     * @var Collection The reviews of the movie.
     */
    #[ORM\OneToMany(mappedBy: 'movie', targetEntity: Review::class)]
    #[Expose]
    private Collection $reviews;

    /**
     * The constructor of the Movie class.
     */
    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }

    /**
     * This method is used to get the id of the movie.
     * @return int|null The id of the movie.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * This method is used to set the id of the movie.
     * @param int $id The id of the movie.
     * @return Movie
     */
    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * This method is used to get the title of the movie.
     * @return string|null The title of the movie.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * This method is used to set the title of the movie.
     * @param string $title The title of the movie.
     * @return Movie
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * This method is used to get the IMDb ID of the movie.
     * @return string|null The IMDb ID of the movie.
     */
    public function getImdbId(): ?string
    {
        return $this->imdbId;
    }

    /**
     * This method is used to set the IMDb ID of the movie.
     * @param string $imdbId The IMDb ID of the movie.
     * @return Movie
     */
    public function setImdbId(string $imdbId): static
    {
        $this->imdbId = $imdbId;

        return $this;
    }

    /**
     * This method is used to get the overview of the movie.
     * @return string|null The overview of the movie.
     */
    public function getOverview(): ?string
    {
        return $this->overview;
    }

    /**
     * This method is used to set the overview of the movie.
     * @param string|null $overview The overview of the movie.
     * @return Movie
     */
    public function setOverview(?string $overview): static
    {
        $this->overview = $overview;

        return $this;
    }

    /**
     * This method is used to get the poster of the movie.
     * @return string|null The poster of the movie.
     */
    public function getPoster(): ?string
    {
        return $this->poster;
    }

    /**
     * This method is used to set the poster of the movie.
     * @param string|null $poster The poster of the movie.
     * @return Movie
     */
    public function setPoster(?string $poster): static
    {
        $this->poster = $poster;

        return $this;
    }

    /**
     * This method is used to get the running time of the movie in minutes.
     * @return int|null The running time of the movie in minutes.
     */
    public function getRunningTime(): ?int
    {
        return $this->runningTime;
    }

    /**
     * This method is used to set the running time of the movie in minutes.
     * @param int|null $runningTime The running time of the movie in minutes.
     * @return Movie
     */
    public function setRunningTime(?int $runningTime): static
    {
        $this->runningTime = $runningTime;

        return $this;
    }

    /**
     * This method is used to get the actors in the movie.
     * @return array|null The actors in the movie.
     */
    public function getActors(): ?array
    {
        return $this->actors;
    }

    /**
     * This method is used to set the actors in the movie.
     * @param array|null $actors The actors in the movie.
     * @return Movie
     */
    public function setActors(?array $actors): static
    {
        $this->actors = $actors;

        return $this;
    }

    /**
     * This method is used to get the directors of the movie.
     * @return array|null The directors of the movie.
     */
    public function getDirectors(): ?array
    {
        return $this->directors;
    }

    /**
     * This method is used to set the directors of the movie.
     * @param array|null $directors The directors of the movie.
     * @return Movie
     */
    public function setDirectors(?array $directors): static
    {
        $this->directors = $directors;

        return $this;
    }

    /**
     * This method is used to get the reviews of the movie.
     * @return Collection The reviews of the movie.
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    /**
     * This method is used to set the reviews of the movie.
     * @param Collection $reviews The reviews of the movie.
     * @return void
     */
    public function setReviews(Collection $reviews): void
    {
        $this->reviews = $reviews;
    }

    /**
     * This method is used to add a review to the movie.
     * @param Review $review The review to be added to the movie.
     * @return void
     */
    public function addReview(Review $review): void
    {
        $this->reviews->add($review);
    }

    /**
     * This method is used to remove a review from the movie.
     * @param Review $review The review to be removed from the movie.
     * @return void
     */
    public function removeReview(Review $review): void
    {
        $this->reviews->removeElement($review);
    }

    /**
     * This method is used to calculate the average score of all reviews for this movie.
     * @return float|null The average score of all reviews for this movie.
     */
    public function calculateAverageScore(): ?float
    {
        if ($this->reviews->isEmpty()) {
            return null;
        }

        $totalScore = 0;
        $reviewCount = 0;

        foreach ($this->reviews as $review) {
            if ($review->getScore() !== null) {
                $totalScore += $review->getScore();
                $reviewCount++;
            }
        }

        if ($reviewCount === 0) {
            return null;
        }

        return $totalScore / $reviewCount;
    }
}

