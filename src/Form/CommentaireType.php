<?php

namespace App\Form;

use App\Entity\Commentaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Security;

class CommentaireType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('commentaire', TextareaType::class, [
                'label' => 'Votre commentaire'
            ])
            ->add('note', NumberType::class, [
                'label' => 'Note (de 1 à 5)',
                'attr' => [
                    'min' => 1,
                    'max' => 5
                ]
            ])
            ->add('enregistrer', SubmitType::class,[
                "attr" => [
                    'class' => "buttonA", 
                ] 
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $commentaire = $event->getData();
            $form = $event->getForm();

            if (!$commentaire || null === $commentaire->getId()) {
                $commentaire->setIdUser($this->security->getUser());
                $commentaire->setDate(new \DateTime());
            }

        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
        ]);
    }
}
