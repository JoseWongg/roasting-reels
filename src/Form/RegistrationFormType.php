<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/**
 * This class is used to create the form for registering a user.
 */
class RegistrationFormType extends AbstractType
{
    /**
     * This method builds the form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Add the fields to the form.
        $builder
            // Add the name field to the form
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => true,
            ])
            // Add an "email" field of type "EmailType"
            ->add('email', EmailType::class)
            // Add a "plainPassword" field of type "RepeatedType" (which contains two "password" fields)
            // The "RepeatedType" field is used to ensure that the user enters the same value twice for the password
            // The "RepeatedType" field renders two "password" fields in the HTML template
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
            ]);

    }

    /**
     * This method configures options for the form. Relates the form to an entity (To validate the form)
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        // Configures the form to use the User class when the form is submitted.
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
