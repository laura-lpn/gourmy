<?php

namespace App\Entity;

use App\Repository\StepRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StepRepository::class)]
class Step
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $town = null;

    #[ORM\Column(type: 'integer')]
    private ?int $meals = 1;

    #[ORM\Column(type: 'integer')]
    private ?int $position = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Restaurant $restaurant = null;

    #[ORM\ManyToOne(inversedBy: 'steps')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Roadtrip $roadtrip = null;

    #[ORM\ManyToMany(targetEntity: TypeRestaurant::class)]
    private Collection $cuisine;

    public function __construct()
    {
        $this->cuisine = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function setTown(string $town): static
    {
        $this->town = $town;
        return $this;
    }

    public function getMeals(): ?int
    {
        return $this->meals;
    }

    public function setMeals(int $meals): static
    {
        $this->meals = $meals;
        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
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

    public function getRoadtrip(): ?Roadtrip
    {
        return $this->roadtrip;
    }

    public function setRoadtrip(?Roadtrip $roadtrip): static
    {
        $this->roadtrip = $roadtrip;
        return $this;
    }

    /**
     * @return Collection<int, TypeRestaurant>
     */
    public function getCuisine(): Collection
    {
        return $this->cuisine;
    }

    public function addCuisine(TypeRestaurant $cuisine): static
    {
        if (!$this->cuisine->contains($cuisine)) {
            $this->cuisine->add($cuisine);
        }

        return $this;
    }

    public function removeCuisine(TypeRestaurant $cuisine): static
    {
        $this->cuisine->removeElement($cuisine);
        return $this;
    }
}