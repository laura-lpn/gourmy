<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'Ce slug est déjà utilisé.')]
#[UniqueEntity(fields: ['name'], message: 'Ce nom de restaurant est déjà utilisé.')]
class Restaurant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^(0[1-9]|[1-8][0-9]|9[0-5])[0-9]{3}$/',
        message: 'Le code postal doit être valide (5 chiffres, ex : 75001).'
    )]
    private ?string $postalCode = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Regex(
        pattern: '/^(-?([0-8]?[0-9](\.\d+)?|90(\.0+)?))$/',
        message: 'La latitude doit être un nombre entre -90 et 90.'
    )]
    private ?float $latitude = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Regex(
        pattern: '/^(-?((1[0-7][0-9]|0?\d{1,2})(\.\d+)?|180(\.0+)?))$/',
        message: 'La longitude doit être un nombre entre -180 et 180.'
    )]
    private ?float $longitude = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['€', '€€', '€€€', '€€€€'], message: 'Choisissez une fourchette de prix valide.')]
    private ?string $priceRange = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(
        protocols: ['http', 'https'],
        message: 'L\'URL du site web doit être valide et commencer par http:// ou https://.'
    )]
    private ?string $website = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $banner = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^\d{14}$/',
        message: 'Le numéro SIRET doit contenir exactement 14 chiffres.'
    )]
    private ?string $siret = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isValided = null;

    #[ORM\OneToOne(inversedBy: 'restaurant', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

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

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
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

    public function getBanner(): ?string
    {
        return $this->banner;
    }

    public function setBanner(string $banner): static
    {
        $this->banner = $banner;

        return $this;
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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        // Detach old owner
        if ($this->owner !== null && $owner === null) {
            $this->owner->setRestaurant(null);
        }

        // Sync inverse side
        if ($owner !== null && $owner->getRestaurant() !== $this) {
            $owner->setRestaurant($this);
        }

        $this->owner = $owner;

        return $this;
    }
}
