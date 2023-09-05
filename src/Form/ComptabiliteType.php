<?php

namespace App\Form;

use App\Entity\Comptabilite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ComptabiliteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_comptabilite' , DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_data' => null,
                
                
                'constraints' => [
                    new LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date doit être inférieure ou égale à la date d\'aujourd\'hui '
                    ])],
                    ])
            
                ->add('valeur',NumberType::class , array(
                    'scale' => 2,
                    'attr' => array(
                        'placeholder' => '00.00'),
                    'invalid_message' => 'la valeur doit être un nombre.'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comptabilite::class,
        ]);
    }
}
