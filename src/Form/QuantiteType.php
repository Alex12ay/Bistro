<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\Quantite;
use App\Entity\Recette;
use App\Entity\UniteDeMesure;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuantiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
            
            ->add('ingredient', EntityType::class, [
                'class' => Ingredient::class,
                'choices'=> $options['ingredientTrier'],
                'choice_label' => 'nom',
            ])
            ->add('quantite')
            ->add('unite', EntityType::class, [
                'class' => UniteDeMesure::class,
                'choice_label' => 'nom',
            ])
            ->add('Enregistrer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quantite::class,
            'ingredientTrier' => [], 
        ]);
    }
}
