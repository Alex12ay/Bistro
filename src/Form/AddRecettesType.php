<?php

namespace App\Form;

use App\Entity\Budget;
use App\Entity\Categorie;
use App\Entity\Difficulte;
use App\Entity\Recette;
use App\Entity\Saison;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true, // Transforme le select en checkboxes
                'label' => 'Catégories',
            ])
            ->add('difficulte', EntityType::class, [
                'class' => Difficulte::class,
                'choice_label' => 'nom',
                'multiple' => false,
                'expanded' => true, // Transforme le select en checkboxes
                'label' => 'Difficulté',
                'label_attr' => ['class' => 'checkbox-inline'],
                'choice_attr' => function($choice, $key, $value) {
                    return [ 
                        'class' => 'form-check-input'
                    ];
                },
            ])
            ->add('budget', EntityType::class, [
                'class' => Budget::class,
                'choice_label' => 'nom',
                'multiple' => false,
                'expanded' => true, // Transforme le select en checkboxes
                'label' => 'Budget',
                'label_attr' => ['class' => 'checkbox-inline'],
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'form-check-input'];
                },
            ])
            ->add('saison', EntityType::class, [
                'class' => Saison::class,
                'choice_label' => 'nom',
                'multiple' => false,
                'expanded' => true, // Transforme le select en checkboxes
                'label' => 'Saison',
                'label_attr' => ['class' => 'checkbox-inline'],
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'form-check-input'];
                },
            ])
            ->add('enregistrer', SubmitType::class,[
                    "attr" => [
                        'class' => "buttonA", 
                    ] 
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
