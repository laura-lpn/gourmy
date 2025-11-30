<?php

namespace App\Entity;

use App\Repository\RestaurantImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RestaurantImageRepository::class)]
#[Vich\Uploadable]
class RestaurantImage extends BaseEntity
{
    #[Vich\UploadableField(mapping: 'restaurants_images', fileNameProperty: 'imageName')]
    #[Assert\Image(maxSize: "2M", mimeTypes: ["image/jpeg", "image/png", "image/webp"])]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\ManyToOne(targetEntity: Restaurant::class, inversedBy: 'images')]
    private ?Restaurant $restaurant = null;

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
    }
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }
    public function getImageName(): ?string
    {
        return $this->imageName;
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
}
