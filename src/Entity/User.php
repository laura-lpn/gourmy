<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[Vich\Uploadable]
class User extends BaseEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(length: 255)]
    #[Assert\Email(message: 'L\'adresse email n\'est pas valide')]
    #[Assert\NotBlank(message: 'L\'adresse email est obligatoire')]
    #[Assert\Length(max: 255, maxMessage: 'L\'adresse email ne doit pas dépasser {{ limit }} caractères')]
    private string $email;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\Length(
        min: 8,
        max: 64,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le mot de passe ne doit pas dépasser {{ limit }} caractères.',
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/',
        message: 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial.',
    )]
    private string $password;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Le nom d\'utilisateur est obligatoire')]
    #[Assert\Length(min: 3, max: 20, minMessage: "Le nom d'utilisateur doit contenir au moins {{ limit }} caractères.", maxMessage: "Le nom d'utilisateur ne peut pas contenir plus de {{ limit }} caractères.")]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_]+$/',
        message: 'Le nom d\'utilisateur ne doit contenir que des lettres, des chiffres et des tirets bas.',
    )]
    private string $username;

    #[ORM\Column]
    #[Assert\Type(type: 'bool', message: 'La valeur doit être de type booléen.')]
    private bool $isVerified = false;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Restaurant $restaurant = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[Assert\Length(max: 255, maxMessage: 'Le prénom ne doit pas dépasser {{ limit }} caractères')]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\s-]+$/',
        message: 'Le prénom ne doit contenir que des lettres, des espaces et des tirets.',
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de famille est obligatoire')]
    #[Assert\Length(max: 255, maxMessage: 'Le nom de famille ne doit pas dépasser {{ limit }} caractères')]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\s-]+$/',
        message: 'Le nom de famille ne doit contenir que des lettres, des espaces et des tirets.',
    )]
    private ?string $lastName = null;

    #[Vich\UploadableField(mapping: 'users_avatar', fileNameProperty: 'avatarName')]

    private ?File $avatarFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $avatarName = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $avatarUpdatedAt = null;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $reviews;

    /**
     * @var Collection<int, Roadtrip>
     */
    #[ORM\OneToMany(targetEntity: Roadtrip::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $roadtrips;

    public function __construct()
    {
        parent::__construct();
        $this->reviews = new ArrayCollection();
        $this->roadtrips = new ArrayCollection();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function setAvatarFile(?File $avatar = null): void
    {
        $this->avatarFile = $avatar;

        if (null !== $avatar) {
            $this->avatarUpdatedAt = new \DateTimeImmutable();
        }
    }

    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    public function setAvatarName(?string $avatarName): void
    {
        $this->avatarName = $avatarName;
    }

    public function getAvatarName(): ?string
    {
        return $this->avatarName;
    }

    public function getAvatarUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->avatarUpdatedAt;
    }

    public function __serialize(): array
    {
        $data = get_object_vars($this);
        unset($data['avatarFile']);
        return $data;
    }

    public function __unserialize(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
            if ($key === 'avatarFile') {
                $this->avatarUpdatedAt = new \DateTimeImmutable();
            }
        }
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setAuthor($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getAuthor() === $this) {
                $review->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Roadtrip>
     */
    public function getRoadtrips(): Collection
    {
        return $this->roadtrips;
    }

    public function addRoadtrip(Roadtrip $roadtrip): static
    {
        if (!$this->roadtrips->contains($roadtrip)) {
            $this->roadtrips->add($roadtrip);
            $roadtrip->setAuthor($this);
        }

        return $this;
    }

    public function removeRoadtrip(Roadtrip $roadtrip): static
    {
        if ($this->roadtrips->removeElement($roadtrip)) {
            // set the owning side to null (unless already changed)
            if ($roadtrip->getAuthor() === $this) {
                $roadtrip->setAuthor(null);
            }
        }

        return $this;
    }
}
