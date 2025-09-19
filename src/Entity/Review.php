<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * This class represents a review in the database.
 */
#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ExclusionPolicy("all")] // Sets the overall exclusion policy with a default serialization behavior to exclude all properties
class Review
{
    /**
     * @var int|null The id of the review.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Expose] // Exposes the property to serialization
    #[Groups(['review_list', 'review_detail'])] // Add the property to the "review_list" and 'review_detail' groups
    private ?int $id = null;

    /**
     * @var User The user who wrote the review.
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[Expose]
    #[Groups(['review_list', 'review_detail'])]
    private User $user;

    /**
     * @var Movie The movie that the review is about.
     */
    #[ORM\ManyToOne(targetEntity: Movie::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(name: 'movie_id', referencedColumnName: 'id')]
    #[Expose]
    #[Groups(['review_list', 'review_detail'])]
    private Movie $movie;

    /**
     * @var string The title of the review.
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank(message: "The review title must not be blank.")]
    #[Expose]
    #[Groups(['review_list', 'review_detail'])]
    private string $reviewTitle;

    /**
     * @var string The text of the review.
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Expose]
    #[Groups(['review_list', 'review_detail'])]
    private string $reviewText;

    /**
     * @var float|null The score of the review.
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: "Score cannot be blank")]
    #[Assert\Range(
        notInRangeMessage: "Score must be between {{ min }} and {{ max }}",
        min: 1,
        max: 5
    )]
    #[Expose]
    #[Groups(['review_list', 'review_detail'])]
    private ?int $score = null;


    /**
     * @var \DateTimeInterface|null The date of the review.
     */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Expose]
    #[Groups(['review_list', 'review_detail'])]
    private ?\DateTimeInterface $date = null;

    /**
     * This method is used to get the id of the review.
     * @return int|null The id of the review.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * This method is used to set the id of the review.
     * @param int|null $id The id of the review.
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * This method is used to get the user who wrote the review.
     * @return User The user who wrote the review.
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * This method is used to set the user who wrote the review.
     * @param User $user The user who wrote the review.
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * This method is used to get the movie that the review is about.
     * @return Movie The movie that the review is about.
     */
    public function getMovie(): Movie
    {
        return $this->movie;
    }

    /**
     * This method is used to set the movie that the review is about.
     * @param Movie $movie The movie that the review is about.
     * @return void
     */
    public function setMovie(Movie $movie): void
    {
        $this->movie = $movie;
    }

    /**
     * This method is used to get the title of the review.
     * @return string The title of the review.
     */
    public function getReviewTitle(): string
    {
        return $this->reviewTitle;
    }

    /**
     * This method is used to set the title of the review.
     * @param string $reviewTitle The title of the review.
     * @return void
     */
    public function setReviewTitle(string $reviewTitle): void
    {
        $this->reviewTitle = $reviewTitle;
    }

    /**
     * This method is used to get the score of the review.
     * @return float|null The score of the review.
     * @return int|null The score of the review.
     */
    public function getScore(): ?float
    {
        return $this->score;
    }

    /**
     * This method is used to set the score of the review.
     * @param float|null $score The score of the review.
     * @param int|null $score The score of the review.
     */
    public function setScore(?float $score): void
    {
        $this->score = $score;
    }

    /**
     * This method is used to get the date of the review.
     * @return \DateTimeInterface|null The date of the review.
     */
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    /**
     * This method is used to set the date of the review.
     * @param \DateTimeInterface $date The date of the review.
     * @return Review
     */
    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }


    /**
     * This method is used to get the text of the review.
     * @return string The text of the review.
     */
    public function getReviewText(): string
    {
        return $this->reviewText;
    }

    /**
     * This method is used to set the text of the review.
     * @param string $reviewText The text of the review.
     * @return void
     */
    public function setReviewText(string $reviewText): void
    {
        $this->reviewText = $reviewText;
    }

    /**
     * This method is used to get the string representation of the review.
     * @return string The string representation of the review.
     */
    public function __toString(): string
    {
        return $this->reviewText;
    }
}