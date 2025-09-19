<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/**
 * This class is used to create the form for logging in.
 */
class LoginFormType extends AbstractType
{
    /**
     * This method builds the form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'inputEmail',
                    'required' => true,
                    'autofocus' => true
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'inputPassword',
                    'required' => true
                ]
            ]);
    }
}