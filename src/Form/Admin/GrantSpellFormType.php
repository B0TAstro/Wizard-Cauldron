<?php
namespace App\Form\Admin;

use App\Entity\Spell;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GrantSpellFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $b, array $options): void
    {
        $b->add('spell', EntityType::class, [
            'class'        => Spell::class,
            'choice_label' => fn(Spell $s) => sprintf('%s — %s', $s->getName(), ucfirst($s->getRarity())),
            'placeholder'  => 'Select a spell',
            'query_builder' => $options['qb'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['qb' => null]);
        $resolver->setAllowedTypes('qb', ['null','callable']);
    }
}
