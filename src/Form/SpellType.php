<?php

namespace App\Form;

use App\Entity\Spell;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpellType extends AbstractType
{
    public function buildForm(FormBuilderInterface $b, array $options): void
    {
        $b
            ->add('name')
            ->add('slug')
            ->add('rarity', ChoiceType::class, [
                'choices' => [
                    'Common' => 'common',
                    'Rare' => 'rare',
                    'Epic' => 'epic',
                    'Legendary' => 'legendary',
                ],
            ])
            ->add('imageUrl', UrlType::class, [
                'required' => false,
                'default_protocol' => 'https',
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['rows' => 5],
            ])
            ->add('isActive')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Spell::class]);
    }
}
