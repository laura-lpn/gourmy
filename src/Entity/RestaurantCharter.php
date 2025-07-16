<?php

namespace App\Entity;

use App\Repository\RestaurantCharterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RestaurantCharterRepository::class)]
class RestaurantCharter extends BaseEntity
{
    #[ORM\OneToOne(inversedBy: 'charter', targetEntity: Restaurant::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Restaurant $restaurant = null;

    #[ORM\Column(type: 'boolean')]
    private bool $usesLocalProducts = false;

    #[ORM\Column(type: 'boolean')]
    private bool $homemadeCuisine = false;

    #[ORM\Column(type: 'boolean')]
    private bool $wasteReduction = false;

    #[ORM\Column(type: 'boolean')]
    private bool $transparentOrigin = false;

    #[ORM\Column(type: 'boolean')]
    private bool $professionalRepliesToReviews = false;

    #[ORM\Column(type: 'boolean')]
    private bool $acceptsModeration = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $validatedByModerator = false;

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(Restaurant $restaurant): self
    {
        $this->restaurant = $restaurant;
        return $this;
    }

    public function isUsesLocalProducts(): bool
    {
        return $this->usesLocalProducts;
    }

    public function setUsesLocalProducts(bool $usesLocalProducts): self
    {
        $this->usesLocalProducts = $usesLocalProducts;
        return $this;
    }

    public function isHomemadeCuisine(): bool
    {
        return $this->homemadeCuisine;
    }

    public function setHomemadeCuisine(bool $homemadeCuisine): self
    {
        $this->homemadeCuisine = $homemadeCuisine;
        return $this;
    }

    public function isWasteReduction(): bool
    {
        return $this->wasteReduction;
    }

    public function setWasteReduction(bool $wasteReduction): self
    {
        $this->wasteReduction = $wasteReduction;
        return $this;
    }

    public function isTransparentOrigin(): bool
    {
        return $this->transparentOrigin;
    }

    public function setTransparentOrigin(bool $transparentOrigin): self
    {
        $this->transparentOrigin = $transparentOrigin;
        return $this;
    }

    public function isProfessionalRepliesToReviews(): bool
    {
        return $this->professionalRepliesToReviews;
    }

    public function setProfessionalRepliesToReviews(bool $professionalRepliesToReviews): self
    {
        $this->professionalRepliesToReviews = $professionalRepliesToReviews;
        return $this;
    }

    public function isAcceptsModeration(): bool
    {
        return $this->acceptsModeration;
    }

    public function setAcceptsModeration(bool $acceptsModeration): self
    {
        $this->acceptsModeration = $acceptsModeration;
        return $this;
    }

    public function isValidatedByModerator(): bool
    {
        return $this->validatedByModerator;
    }

    public function setValidatedByModerator(bool $validatedByModerator): self
    {
        $this->validatedByModerator = $validatedByModerator;
        return $this;
    }
}