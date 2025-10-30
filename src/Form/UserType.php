<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isCreate = (bool) ($options['require_password'] ?? false);
        $builder
            ->add('pseudo', null, [
                'label' => 'Username',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 3, max: 32),
                    new Assert\Regex('/^[A-Za-z0-9_.]+$/'),
                ],
                'attr' => ['class' => 'w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2'],
            ])
            ->add('email', null, [
                'constraints' => [new Assert\NotBlank(), new Assert\Email()],
                'attr' => ['class' => 'w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2'],
            ])
            ->add('coins', IntegerType::class, [
                'attr' => ['min' => 0, 'class' => 'w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2'],
            ])
            ->add('roles', ChoiceType::class, [
                'choices'  => ['Admin' => 'ROLE_ADMIN'],
                'expanded' => true,
                'multiple' => true,
                'label'    => 'Roles',
            ])
            ->add('newPassword', PasswordType::class, [
                'mapped' => false,
                'required' => $isCreate, 
                'label' => $isCreate ? 'Password' : 'New password',
                'attr' => [
                    'autocomplete' => $isCreate ? 'new-password' : 'off',
                    'class' => 'w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2'
                ],
                'constraints' => array_filter([
                    $isCreate ? new Assert\NotBlank() : null,
                    new Assert\Length(min: 6, minMessage: 'At least {{ limit }} characters'),
                ]),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'        => User::class,
            'require_password'  => false,
        ]);
    }
}
