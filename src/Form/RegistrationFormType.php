<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', null, [
                'label' => 'Username',
                'required' => true,
                'attr'  => ['autocomplete' => 'username'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a username']),
                    new Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Your username should be at least {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('email', null, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Please enter an email']),
                    new Email(['message' => 'Please enter a valid email address']),
                    new Length(['max' => 180]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password',
                'mapped' => false,
                'required' => true,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a password']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}