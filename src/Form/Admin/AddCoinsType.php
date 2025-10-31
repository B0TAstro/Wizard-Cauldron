<?php
namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class AddCoinsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $b, array $opts): void
    {
        $b->add('amount', IntegerType::class, [
            'label' => 'Coins',
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Range(min: -100, max: 100, notInRangeMessage: 'Must be between {{ min }} and {{ max }}.'),
            ],
            'attr' => [
                'min' => -100, 'max' => 100, 'step' => 1,
            ],
        ]);
    }
}