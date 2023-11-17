<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'attr' => [
                    'class' => 'mb-32 ml-32']
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'mb-32 ml-32']
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'mb-32 ml-32']
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'class' => 'mb-32 ml-32']
            ])
            ->add('mail', EmailType::class, [
                'label' => 'Mail',
                'attr' => [
                    'class' => 'mb-32 ml-32']
            ])
            ->add('motPasse', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent être identiques.',
                'mapped' => false,
                'required' => false,
                'first_options' => ['label' => 'Mot de passe',
                    'attr' => [
                        'class' => 'mb-32 ml-32']],
                'second_options' => ['label' => 'Répétez le mot de passe',
                    'attr' => [
                        'class' => 'mb-32 ml-32']],
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'label' => 'Choix du campus',
                'expanded' => false,
                'multiple' => false,
                'attr' => [
                    'class' => 'mb-32 ml-32']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
