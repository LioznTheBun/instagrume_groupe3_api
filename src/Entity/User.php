<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    #[OA\Property(type: "array", items: new OA\Items(type: "string"))]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $avatar = null;

    #[ORM\Column(length: 64)]
    private ?string $pseudo = null;

    #[ORM\OneToMany(mappedBy: 'auteur', targetEntity: Publication::class)]
    private Collection $publications;

    #[ORM\OneToMany(mappedBy: 'auteur', targetEntity: Commentaire::class)]
    private Collection $commentaires;

    #[ORM\Column]
    private ?bool $isBanned = null;

    #[ORM\OneToMany(mappedBy: 'user_id', targetEntity: ArrayRatingCom::class, orphanRemoval: true)]
    private Collection $arrayRatingComs;

    #[ORM\OneToMany(mappedBy: 'user_id', targetEntity: ArrayRatingPost::class, orphanRemoval: true)]
    private Collection $arrayRatingPosts;

    public function __construct()
    {
        $this->publications = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->arrayRatingComs = new ArrayCollection();
        $this->arrayRatingPosts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * @return Collection<int, Publication>
     */
    public function getPublications(): Collection
    {
        return $this->publications;
    }

    public function addPublication(Publication $publication): static
    {
        if (!$this->publications->contains($publication)) {
            $this->publications->add($publication);
            $publication->setAuteur($this);
        }

        return $this;
    }

    public function removePublication(Publication $publication): static
    {
        if ($this->publications->removeElement($publication)) {
            // set the owning side to null (unless already changed)
            if ($publication->getAuteur() === $this) {
                $publication->setAuteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setAuteur($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getAuteur() === $this) {
                $commentaire->setAuteur(null);
            }
        }

        return $this;
    }

    public function isIsBanned(): ?bool
    {
        return $this->isBanned;
    }

    public function setIsBanned(bool $isBanned): static
    {
        $this->isBanned = $isBanned;

        return $this;
    }

    /**
     * @return Collection<int, ArrayRatingCom>
     */
    public function getArrayRatingComs(): Collection
    {
        return $this->arrayRatingComs;
    }

    public function addArrayRatingCom(ArrayRatingCom $arrayRatingCom): static
    {
        if (!$this->arrayRatingComs->contains($arrayRatingCom)) {
            $this->arrayRatingComs->add($arrayRatingCom);
            $arrayRatingCom->setUserId($this);
        }

        return $this;
    }

    public function removeArrayRatingCom(ArrayRatingCom $arrayRatingCom): static
    {
        if ($this->arrayRatingComs->removeElement($arrayRatingCom)) {
            // set the owning side to null (unless already changed)
            if ($arrayRatingCom->getUserId() === $this) {
                $arrayRatingCom->setUserId(null);
            }
        }

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
            $arrayRatingPost->setUserId($this);
        }

        return $this;
    }

    public function removeArrayRatingPost(ArrayRatingPost $arrayRatingPost): static
    {
        if ($this->arrayRatingPosts->removeElement($arrayRatingPost)) {
            // set the owning side to null (unless already changed)
            if ($arrayRatingPost->getUserId() === $this) {
                $arrayRatingPost->setUserId(null);
            }
        }

        return $this;
    }
}
