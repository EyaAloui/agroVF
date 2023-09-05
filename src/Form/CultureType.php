<?php

namespace App\Form;

use App\Entity\Culture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class CultureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', TextType::class, [
                'constraints' => [
                    new LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'The date must be less than or equal to today\'s date.'
                    ])
                ],
            ],)
            // ->add('date_planting',DateType::class)
            ->add ('date_planting', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_data' => null,
                'constraints' => [
                    new LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'il faut ajouter une date precise avant ce jour  '],)], ],)
            ->add('quantite',NumberType::class , array(
                'scale' => 2,
                'attr' => array(
                    'placeholder' => '0.00'),
                'invalid_message' => 'la quantité doit être un nombre.'))
            ->add('ajouter',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Culture::class,
        ]);
    }
}