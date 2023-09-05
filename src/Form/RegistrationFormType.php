<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\CallbackTransformer;
use App\Form\FormExtension\RepeatedPasswordType;

class RegistrationFormType extends AbstractType
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
        ->add('nom',TextType::class,
        ['attr'=>['class'=>'form-control']])
        ->add('prenom' ,TextType::class,
        ['attr'=>['class'=>'form-control']])
        ->add('telephone',TextType::class,
        ['attr'=>['class'=>'form-control']])
        ->add('cin',TextType::class,
        ['attr'=>['class'=>'form-control']])  
            ->add('email',EmailType::class,
            ['attr'=>['class'=>'form-control']])
            
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('password', RepeatedPasswordType::class)
        ;
       
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
