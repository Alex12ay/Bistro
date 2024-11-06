<?php

namespace App\Entity;

use App\Repository\QuantiteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuantiteRepository::class)]
class Quantite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\ManyToOne(inversedBy: 'quantites')]
    private ?Ingredient $ingredient = null;

    #[ORM\ManyToOne(inversedBy: 'quantites')]
    private ?Recette $recette = null;

    #[ORM\ManyToOne(inversedBy: 'quantites')]
    private ?UniteDeMesure $unite = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(float $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getIngredient(): ?Ingredient
    {
        return $this->ingredient;
    }

    public function setIngredient(?Ingredient $ingredient): static
    {
        $this->ingredient = $ingredient;

        return $this;
    }

    public function getRecette(): ?Recette
    {
        return $this->recette;
    }

    public function setRecette(?Recette $recette): static
    {
        $this->recette = $recette;

        return $this;
    }

    public function getUnite(): ?UniteDeMesure
    {
        return $this->unite;
    }

    public function setUnite(?UniteDeMesure $unite): static
    {
        $this->unite = $unite;

        return $this;
    }
}
