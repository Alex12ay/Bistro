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


    /**
     * @var Collection<int, Recette>
     */
    #[ORM\ManyToMany(targetEntity: Recette::class, inversedBy: 'ingredients')]
    private Collection $recette;

    public function __construct()
    {
        $this->nom = new ArrayCollection();
        $this->quantites = new ArrayCollection();
        $this->recette = new ArrayCollection();

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
     * @return Collection<int, RecetteIngredient>
     */
    public function getRecetteIngredients(): Collection
    {
        return $this->recetteIngredients;
    }

    public function addRecetteIngredients(RecetteIngredient $recette): static
    {
        if (!$this->recetteIngredients->contains($recette)) {
            $this->recetteIngredients->add($recette);
            $recette->setIngredient($this);
        }

        return $this;
    }

    public function removeRecetteIngredients(RecetteIngredient $recette): static
    {
        if ($this->recetteIngredients->removeElement($recette)) {
            // set the owning side to null (unless already changed)
            if ($recette->getIngredient() === $this) {
                $recette->setIngredient(null);
            }
        }

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

    /**
     * @return Collection<int, Recette>
     */
    public function getRecette(): Collection
    {
        return $this->recette;
    }

    public function addRecette(Recette $recette): static
    {
        if (!$this->recette->contains($recette)) {
            $this->recette->add($recette);
        }

        return $this;
    }

    public function removeRecette(Recette $recette): static
    {
        $this->recette->removeElement($recette);

        return $this;
    }

    public function __toString()
    {
        return $this->getNom();
    }


}
