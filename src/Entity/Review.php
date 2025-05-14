<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[Vich\Uploadable]
class Review extends BaseEntity
{
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $comment = null;

    #[ORM\Column(nullable: true)]
    private ?float $rating = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Restaurant $restaurant = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\OneToOne(targetEntity: self::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: "response_id", referencedColumnName: "id", nullable: true, onDelete: "CASCADE")]
    private ?self $response = null;

    #[ORM\OneToOne(mappedBy: 'response', targetEntity: self::class)]
    private ?self $originalReview = null;

    #[Vich\UploadableField(mapping: 'reviews_image', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $imageUpdatedAt = null;

    public function isResponse(): bool
    {
        return $this->originalReview !== null;
    }

    public function canBeAnswered(): bool
    {
        return $this->response === null;
    }

    #[Assert\Callback]
    public function validateRating(ExecutionContextInterface $context): void
    {
        if ($this->originalReview === null && $this->rating === null) {
            $context->buildViolation('La note est obligatoire pour un avis.')
                ->atPath('rating')
                ->addViolation();
        }

        if ($this->originalReview !== null && $this->rating !== null) {
            $context->buildViolation('Une réponse ne doit pas contenir de note.')
                ->atPath('rating')
                ->addViolation();
        }
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): static
    {
        $this->rating = $rating;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getResponse(): ?self
    {
        return $this->response;
    }

    public function setResponse(?self $response): static
    {
        $this->response = $response;

        return $this;
    }

    public function getOriginalReview(): ?self
    {
        return $this->originalReview;
    }

    public function setOriginalReview(?self $originalReview): static
    {
        $this->originalReview = $originalReview;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): static
    {
        $this->imageFile = $imageFile;

        if ($imageFile) {
            $this->imageUpdatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function setImageName(string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function getImageUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->imageUpdatedAt;
    }
}
