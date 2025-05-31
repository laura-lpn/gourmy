<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
#[UniqueEntity(fields: ['name'], message: 'Ce nom de restaurant est déjà utilisé.')]
#[Vich\Uploadable]
#[HasLifecycleCallbacks]
class Restaurant extends BaseEntity
{
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ['Default'])]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(groups: ['Default'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ['Default'])]
    #[Assert\Length(max: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ['Default'])]
    #[Assert\Length(max: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ['Default'])]
    #[Assert\Regex(
        pattern: '/^(0[1-9]|[1-8][0-9]|9[0-5])[0-9]{3}$/',
        message: 'Le code postal doit être valide (5 chiffres, ex : 75001).'
    )]
    private ?string $postalCode = null;

    #[ORM\Column]
    #[Assert\NotNull(groups: ['strict'])]
    #[Assert\Regex(
        pattern: '/^(-?([0-8]?[0-9](\.\d+)?|90(\.0+)?))$/',
        message: 'La latitude doit être un nombre entre -90 et 90.'
    )]
    private ?float $latitude = null;

    #[ORM\Column]
    #[Assert\NotNull(groups: ['strict'])]
    #[Assert\Regex(
        pattern: '/^(-?((1[0-7][0-9]|0?\d{1,2})(\.\d+)?|180(\.0+)?))$/',
        message: 'La longitude doit être un nombre entre -180 et 180.'
    )]
    private ?float $longitude = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ['Default'])]
    #[Assert\Choice(choices: ['€', '€€', '€€€', '€€€€'], message: 'Choisissez une fourchette de prix valide.')]
    private ?string $priceRange = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'L\'URL fournie n\'est pas valide.')]
    private ?string $website = null;

    #[Vich\UploadableField(mapping: 'restaurants_banner', fileNameProperty: 'bannerName')]

    private ?File $bannerFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $bannerName = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $bannerUpdatedAt = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ['Default'])]
    #[Assert\Regex(
        pattern: '/^\d{14}$/',
        message: 'Le numéro SIRET doit contenir exactement 14 chiffres.'
    )]
    private ?string $siret = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isValided = false;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le numéro de téléphone est obligatoire.', groups: ['Default'])]
    #[Assert\Regex(
        pattern: '/^\+?[0-9\s\-().]{7,20}$/',
        message: 'Le numéro de téléphone n’est pas valide.'
    )]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(groups: ['Default'])]
    #[Assert\Length(
        max: 1000,
        maxMessage: 'Les horaires d’ouverture ne doivent pas dépasser {{ limit }} caractères.'
    )]
    private ?string $openingHours = null;

    #[ORM\OneToOne(mappedBy: 'restaurant', targetEntity: User::class)]
    private ?User $owner = null;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'restaurant', orphanRemoval: true, cascade: ['remove'])]
    private Collection $reviews;

    public function __construct()
    {
        parent::__construct();
        $this->reviews = new ArrayCollection();
    }

    #[PrePersist]
    public function setSlug(): void
    {
        if (null === $this->slug) {
            $this->slug = strtolower(str_replace(' ', '-', $this->name));
        }
    }

    #[PreUpdate]
    public function updateSlug(): void
    {
        if (null !== $this->name) {
            $this->slug = strtolower(str_replace(' ', '-', $this->name));
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getPriceRange(): ?string
    {
        return $this->priceRange;
    }

    public function setPriceRange(string $priceRange): static
    {
        $this->priceRange = $priceRange;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function setBannerFile(?File $banner = null): void
    {
        $this->bannerFile = $banner;

        if (null !== $banner) {
            $this->bannerUpdatedAt = new \DateTimeImmutable();
        }
    }

    public function getBannerFile(): ?File
    {
        return $this->bannerFile;
    }

    public function setBannerName(?string $bannerName): void
    {
        $this->bannerName = $bannerName;
    }

    public function getBannerName(): ?string
    {
        return $this->bannerName;
    }

    public function getBannerUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->bannerUpdatedAt;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function isValided(): ?bool
    {
        return $this->isValided;
    }

    public function setValided(?bool $isValided): static
    {
        $this->isValided = $isValided;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getOpeningHours(): ?string
    {
        return $this->openingHours;
    }

    public function setOpeningHours(?string $openingHours): static
    {
        $this->openingHours = $openingHours;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        // unset the owning side of the relation if necessary
        if ($owner === null && $this->owner !== null) {
            $this->owner->setRestaurant(null);
        }

        // set the owning side of the relation if necessary
        if ($owner !== null && $owner->getRestaurant() !== $this) {
            $owner->setRestaurant($this);
        }

        $this->owner = $owner;

        return $this;
    }

    public function __serialize(): array
    {
        $data = get_object_vars($this);
        unset($data['bannerFile']);
        return $data;
    }

    public function __unserialize(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
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
            $review->setRestaurant($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getRestaurant() === $this) {
                $review->setRestaurant(null);
            }
        }

        return $this;
    }
}
