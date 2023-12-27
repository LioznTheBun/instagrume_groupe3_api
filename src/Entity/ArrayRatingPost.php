<?php

namespace App\Entity;

use App\Repository\ArrayRatingPostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArrayRatingPostRepository::class)]
class ArrayRatingPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'arrayRatingPosts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?RatingPublication $rating_publication_id = null;

    #[ORM\ManyToOne(inversedBy: 'arrayRatingPosts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_id = null;

    #[ORM\Column]
    private ?bool $liked = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRatingPublicationId(): ?RatingPublication
    {
        return $this->rating_publication_id;
    }

    public function setRatingPublicationId(?RatingPublication $rating_publication_id): static
    {
        $this->rating_publication_id = $rating_publication_id;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function isLiked(): ?bool
    {
        return $this->liked;
    }

    public function setLiked(bool $liked): static
    {
        $this->liked = $liked;

        return $this;
    }
}
