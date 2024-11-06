<?php

namespace App\Form;

use App\Entity\Budget;
use App\Entity\Categorie;
use App\Entity\Difficulte;
use App\Entity\Recette;
use App\Entity\Saison;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Ustensile;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddRecettesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('image', FileType::class,[
                'mapped' => false,
            ])
            ->add('temps')
            ->add('description')
                  
            ->add('ustensile', EntityType::class, [
                'class' => Ustensile::class,
                'choice_label' => 'nom',
                'multiple' => true,
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'multiple' => true,
            ])

            ->add('difficulte', EntityType::class, [
                'class' => Difficulte::class,
                
            ])
            ->add('budget', EntityType::class, [
                'class' => Budget::class,
                'choice_label' => 'nom',
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'nom',
                'multiple' => true,
            ])
            ->add('saison', EntityType::class, [
                'class' => Saison::class,
                'choice_label' => 'nom',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }

    
}
