<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie : ',
                'attr' => [
                    'class' => 'mb-32 ml-32'
                ]
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de la sortie : ',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'mb-32 ml-32'
                ]
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => "Date limite d'inscription : ",
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'mb-32 ml-32'
                ]
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => "Nombre de places : ",
                'attr' => [
                    'class' => 'mb-32 ml-32'
                ]
            ])
            ->add('duree', IntegerType::class, [
                'label' => "Durée (minutes) : ",
                'attr' => [
                    'class' => 'mb-32 ml-32'
                ]
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => "Description et infos : ",
                'attr' => [
                    'class' => 'mb-32 ml-32'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
