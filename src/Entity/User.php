<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;



/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * This class represents a logged-in User of the application.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(
    fields: ['email'],
    message: 'There is already an account with this email.'
)]
#[ExclusionPolicy("all")] // Sets the overall exclusion policy with a default serialization behavior to exclude all properties
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var int|null The id of the user.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null The email of the user.
     */
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email(
        message: 'The email "{{ value }}" is not a valid email.',
        mode: 'strict'
    )]
    private ?string $email = null;

    /**
     * @var array The roles of the user.
     */
    #[ORM\Column]
    private array $roles = [];


    /**
     * @var string|null The plain password of the user.
     */
    #[Assert\Length(
        min: 8,
        max: 4096,
        minMessage: 'Your password must be at least {{ limit }} characters long',
    )]
    private ?string $plainPassword = null;

    /**
     * @var Collection The reviews of the user.
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Review::class)]
    private Collection $reviews;

    /**
     * @var string The password of the user.
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var string|null The name of the user.
     */
    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank (message: 'Please enter your name.') ]
    #[Expose]
    #[Groups(['review_list', 'review_detail'])]
    private ?string $name = null;

    /**
     * The constructor of the User class.
     */
    public function __construct() {
        $this->roles = [];
        $this->reviews = new ArrayCollection();
    }

    /**
     * This method is used to get the id of the user.
     * @return string
     * Returns the id of the user.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     *
     * This method is used to get the id of the user.
     * @return string|null Returns the email of the user.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     *
     * This method sets the email of the user.
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * This method is used to get the roles of the user.
     * @return array Returns the roles of the user.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * This method is used to set the roles of the user.
     * @param array $roles The roles of the user.
     * @return $this The user object.
     */
    public function setRoles(array $roles): static
    {
        // Remove duplicate values and reset the roles
        $this->roles = array_unique($roles);

        return $this;
    }

    /**
     * This method is used to get the plain password of the user.
     * @return string|null The plain password of the user.
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * This method is used to set the plain password of the user.
     * @param string|null $plainPassword The plain password of the user.
     * @return void
     */
    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * This method is used to get the reviews of the user.
     * @return Collection The reviews of the user.

     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    /**
     *
     * @param Review $review The review to be added to the user.
     * @return $this The user object.
     */
    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setUser($this);
        }

        return $this;
    }

    /**
     * This method is used to get the password of the user.
     * @see PasswordAuthenticatedUserInterface
     * @return string The password of the user.
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * This method is used to set the hashed password of the user.
     * @param string $password The plain password of the user.
     * @return $this The user object.
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * This method is used to get the name of the user.
     * @return string|null The name of the user.
     * @return string|null The name of the user.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * This method is used to set the name of the user.
     * @param string|null $name The name of the user.
     * @return void
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * This method is used to get the user identifier of the user.
     * @return string The user identifier of the user.
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * This method is used to delete the user's plain password.
     * @see UserInterface
     * @return void
     */
    public function eraseCredentials(): void
    {
         $this->plainPassword = null;
    }

    /**
     * This method is used to remove a review from the user.
     * @param Review $review
     * @return $this The user object.
     */
    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getUser() === $this) {
                $review->setUser(null);
            }
        }
        return $this;
    }
}
