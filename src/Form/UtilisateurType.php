<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('roles', ChoiceType::class, [
            'label' => 'Role',
            'required' => true,
            'multiple' => true,
            'expanded' => false,
            'choices'  => [
                'User' => 'ROLE_USER',
                'Admin' => 'ROLE_Admin',
            ],
        ])
            ->add('email' , EmailType::class)
            ->add('password' , PasswordType::class)
            ->add('nom',TextType::class,
            ['attr'=>['class'=>'form-control']])
            ->add('prenom' ,TextType::class,
            ['attr'=>['class'=>'form-control']])
            ->add('telephone',TextType::class,
            ['attr'=>['class'=>'form-control']])
            ->add('cin',TextType::class,
            ['attr'=>['class'=>'form-control']])        ;

       
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
