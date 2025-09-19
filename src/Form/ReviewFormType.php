<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Range;

/**
 * This class is used to create the form for adding a review.
 */
class ReviewFormType extends AbstractType
{

    /**
     * This method builds the form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reviewTitle', TextType::class, ['label' => 'Title'])
            ->add('reviewText', TextareaType::class, ['label' => 'Review'])
            ->add('score', IntegerType::class, [
                'label' => 'Score',
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'max' => 5,
                        'minMessage' => 'The score must be at least {{ limit }}.',
                        'maxMessage' => 'The score cannot be higher than {{ limit }}.'
                    ])
                ]
            ])
            ->add('save', SubmitType::class, ['label' => 'Submit Review']);
    }

    /**
     * This method configures options for the form. Relates the form to an entity (To validate the form)
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}