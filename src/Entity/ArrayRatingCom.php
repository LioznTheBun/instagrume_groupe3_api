<?php

namespace App\Entity;

use App\Repository\ArrayRatingComRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArrayRatingComRepository::class)]
class ArrayRatingCom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'arrayRatingComs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?RatingCommentaire $rating_commentaire_id = null;

    #[ORM\ManyToOne(inversedBy: 'arrayRatingComs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_id = null;

    #[ORM\Column]
    private ?bool $liked = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRatingCommentaireId(): ?RatingCommentaire
    {
        return $this->rating_commentaire_id;
    }

    public function setRatingCommentaireId(?RatingCommentaire $rating_commentaire_id): static
    {
        $this->rating_commentaire_id = $rating_commentaire_id;

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
