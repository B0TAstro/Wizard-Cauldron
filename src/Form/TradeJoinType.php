<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TradeJoinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Formulaire vide : token CSRF
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'trade_join', 
        ]);
    }
}
