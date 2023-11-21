<?php

namespace App\Entity;

use App\Repository\RatingCommentaireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RatingCommentaireRepository::class)]
class RatingCommentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $likesCount = null;

    #[ORM\Column]
    private ?int $dislikesCount = null;

    #[ORM\OneToOne(inversedBy: 'ratingCommentaire', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commentaire $commentaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLikesCount(): ?int
    {
        return $this->likesCount;
    }

    public function setLikesCount(int $likesCount): static
    {
        $this->likesCount = $likesCount;

        return $this;
    }

    public function getDislikesCount(): ?int
    {
        return $this->dislikesCount;
    }

    public function setDislikesCount(int $dislikesCount): static
    {
        $this->dislikesCount = $dislikesCount;

        return $this;
    }

    public function getCommentaire(): ?Commentaire
    {
        return $this->commentaire;
    }

    public function setCommentaire(Commentaire $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }
}
