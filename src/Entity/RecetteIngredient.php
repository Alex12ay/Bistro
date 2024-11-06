<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RecetteIngredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Recette::class, inversedBy: 'recetteIngredients')]
    private $recette;

    #[ORM\ManyToOne(targetEntity: Ingredient::class, inversedBy: 'recetteIngredients')]
    private $ingredient;

    #[ORM\Column(type: 'float', nullable: true)]
    private $quantite;

    // Getters et setters
}
