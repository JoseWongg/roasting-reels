<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Movie;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


// This class represents the form used to create and update movies via the API.
class MovieAPIType extends AbstractType
{
    // This function builds the form used to create and update movies via the API.
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Add the fields to the form
        $builder
            // The title field is a text field
            ->add('title', TextType::class)
            // The year field is a text field
            ->add('imdbId', TextType::class)
            // The overview field is a textarea
            ->add('overview', TextareaType::class, ['required' => false])
            // The poster field is a text field
            ->add('poster', TextType::class, ['required' => false])
            // The runningTime field is an integer field
            ->add('runningTime', IntegerType::class, ['required' => false])
            // The actors field is a collection of text fields
            ->add('actors', CollectionType::class, [
                // The entry_type option specifies the type of form field that will be used for each item in the collection
                'entry_type' => TextType::class,
                // The allow_add option allows new items to be added to the collection
                'allow_add' => true,
                // The prototype option allows the form to be rendered with JavaScript to add new items
                'prototype' => true,
                // The entry_options option specifies the options that will be passed to each item in the collection
                'entry_options' => ['label' => false],
                // The by_reference option specifies whether the collection should be passed by reference
                'by_reference' => false,
            ])
            // The directors field is a collection of text fields
            ->add('directors', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'prototype' => true,
                'entry_options' => ['label' => false],
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Movie::class,
            'csrf_protection' => false, // Turns off CSRF protection
        ]);
    }
}