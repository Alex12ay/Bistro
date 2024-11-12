<?php

namespace App\Form;

use App\Entity\Recette;
use App\Entity\Ustensile;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceUstensileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ustensile', EntityType::class, [
                'class' => Ustensile::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' =>true,
                'label' => 'Ustensiles',
                'required' => false,
                'placeholder' => 'Choisir des ustensiles',
            ])
           
            ->add('Enregistrer_les_ustensiles', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }
}
