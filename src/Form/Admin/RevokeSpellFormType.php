<?php

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class RevokeSpellFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $b, array $options): void
    {
        $b->add('spellId', HiddenType::class);
    }
}
