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
            ->add('nom' , TextType::class, [
                'label'=> 'Nom de la sortie : '
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de la sortie : ',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd HH:mm', // Format de la date et de l'heure
                'attr' => [
                    'placeholder' => 'Ex. 2023-12-31 14:30',
                    'class' => 'datetimepicker', // Ajoutez une classe pour l'intégration avec un sélecteur de date/heure JavaScript
                ],
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => "Date limite d'inscription : ",
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd HH:mm',
                'attr' => [
                    'placeholder' => "Ex. 2023-12-31 18:00", // Exemple du format attendu
                    'class' => 'datetimepicker',
                ],
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label'=> "Nombre de places : "
            ])
            ->add('duree' , IntegerType::class, [
                'label'=> "Durée (minutes) : ",
            ])
            ->add('infosSortie', TextareaType::class, [
                'label'=> "Description et infos : "
            ])
            //->add('lieu')
            //->add('siteOrganisateur')
            //->add('participants')
            //->add('organisateur')
            //->add('etat')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
