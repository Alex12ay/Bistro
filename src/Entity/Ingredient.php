<?php

namespace App\Entity;

use App\Repository\IngredientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IngredientRepository::class)]
class Ingredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $thumbnail = null;

    /**
     * @var Collection<int, Quantite>
     */
    #[ORM\OneToMany(targetEntity: Quantite::class, mappedBy: 'ingredient')]
    private Collection $quantites;

    #[ORM\OneToMany(targetEntity: RecetteIngredient::class, mappedBy: 'ingredient', cascade: ['persist', 'remove'])]
    private $recetteIngredients;

    public function __construct()
    {
        $this->nom = new ArrayCollection();
        $this->quantites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * @return Collection<int, Quantite>
     */
    public function getQuantites(): Collection
    {
        return $this->quantites;
    }

    public function addQuantite(Quantite $quantite): static
    {
        if (!$this->quantites->contains($quantite)) {
            $this->quantites->add($quantite);
            $quantite->setIngredient($this);
        }

        return $this;
    }

    public function removeQuantite(Quantite $quantite): static
    {
        if ($this->quantites->removeElement($quantite)) {
            // set the owning side to null (unless already changed)
            if ($quantite->getIngredient() === $this) {
                $quantite->setIngredient(null);
            }
        }

        return $this;
    }

}
