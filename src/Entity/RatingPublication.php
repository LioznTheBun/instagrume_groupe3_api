<?php

namespace App\Entity;

use App\Repository\RatingPublicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RatingPublicationRepository::class)]
class RatingPublication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $likesCount = null;

    #[ORM\Column]
    private ?int $dislikesCount = null;

    #[ORM\OneToOne(inversedBy: 'ratingPublication', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Publication $publication = null;

    #[ORM\OneToMany(mappedBy: 'rating_publication_id', targetEntity: ArrayRatingPost::class, orphanRemoval: true)]
    private Collection $arrayRatingPosts;

    public function __construct()
    {
        $this->arrayRatingPosts = new ArrayCollection();
    }

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

    public function getPublication(): ?Publication
    {
        return $this->publication;
    }

    public function setPublication(Publication $publication): static
    {
        $this->publication = $publication;

        return $this;
    }

    /**
     * @return Collection<int, ArrayRatingPost>
     */
    public function getArrayRatingPosts(): Collection
    {
        return $this->arrayRatingPosts;
    }

    public function addArrayRatingPost(ArrayRatingPost $arrayRatingPost): static
    {
        if (!$this->arrayRatingPosts->contains($arrayRatingPost)) {
            $this->arrayRatingPosts->add($arrayRatingPost);
            $arrayRatingPost->setRatingPublicationId($this);
        }

        return $this;
    }

    public function removeArrayRatingPost(ArrayRatingPost $arrayRatingPost): static
    {
        if ($this->arrayRatingPosts->removeElement($arrayRatingPost)) {
            // set the owning side to null (unless already changed)
            if ($arrayRatingPost->getRatingPublicationId() === $this) {
                $arrayRatingPost->setRatingPublicationId(null);
            }
        }

        return $this;
    }
}
