<?php

namespace App\Form;

use App\Data\SearchData;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class SearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rechercher', TextType::class, [
                'label' => 'Le nom de la sortie contient : ',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher'
                ]
            ])
            ->add('campus', ChoiceType::class, [
                'choices' => $options['campus_choices'],
                'label' => 'Campus : ',
                'required' => false,
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Entre ',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Et ',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('utilite1', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('utilite2', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('utilite3', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('utilite4', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchData::class,
            'method' => 'GET',
            'csrf_protection' => false,
            'campus_choices' => [],
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
