<?php

namespace App\Form\FormExtension;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;

class RepeatedPasswordType extends AbstractType
{
    public function getParent(): string
    {
        return RepeatedType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            ['type'  =>PasswordType::class,
            'invalid_message' => "Les mots de passe saisis ne correspondent pas.",
            'required' => true,
            'first_options' => [
             'label' => "Mot de passe",
             'attr'=>['class'=>'form-control']
            ],
            'second_options' => [
             'label' => "Confirmer Mot de passe",
             'attr'=>['class'=>'form-control'],
             'label_attr' => [
                 'title' => 'Confirmez votre Mot de passe'
             ],
         ]
         ], [
                 // instead of being set onto the object directly,
                 // this is read and encoded in the controller
                 'mapped' => false,
                 'attr' => ['autocomplete' => 'new-password'],
                 'constraints' => [
                     new NotBlank([
                         'message' => 'SVP saisir votre mot de passe',
                     ]),
                     new Length([
                         'min' => 6,
                         'minMessage' => 'Your password should be at least {{ limit }} characters',
                         // max length allowed by Symfony for security reasons
                         'max' => 4096,
                     ]),
                 ],
             ])
         ;
    }
}