<?php

namespace App\Form;

use App\Entity\Facture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\Date;







class FactureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add ('date_facture', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_data' => null,
               
                
                'constraints' => [
                    new LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date doit être inférieure ou égale à la date d\'aujourd\'hui '
                    ])],
            ])
                        
                    
        
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Achat' => 'Achat',
                    'Vente' => 'Vente',
                ],'empty_data' => null,
                'expanded' => true,
                
            ])
                  ->add('montant_totale',NumberType::class , array(
                    
                    'attr' => array(
                        'placeholder' => 'Montant'),
                    'invalid_message' => 'le montant doit être un nombre réel.'))
                    
                    
                 ->add('comptabilite')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Facture::class,
        ]);
    }

    


}
