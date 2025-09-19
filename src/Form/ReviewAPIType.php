<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewAPIType extends AbstractType
{
    // This function builds the form used to create and update reviews via the API.
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Add the fields to the form
        $builder
            // The reviewTitle field is a text field
            ->add('reviewTitle', TextType::class, [
                'required' => true,
            ])
            // The reviewText field is a textarea
            ->add('reviewText', TextareaType::class, [
                'required' => true,
            ])
            // The score field is a number field
            ->add('score', NumberType::class, [
                'required' => false,
            ]);
        // Note: The movie and user associations are handled in the controller based on URL parameters and authentication context.
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
            'csrf_protection' => false,  // Disable CSRF protection for API endpoint
        ]);
    }
}