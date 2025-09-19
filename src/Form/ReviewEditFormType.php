<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ReviewEditFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('reviewTitle', TextType::class)
            ->add('reviewText', TextareaType::class)
            ->add('save', SubmitType::class, [
                'label' => 'Save Changes',
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->add('delete', SubmitType::class, [
                'label' => 'Delete Review',
                'attr' => [
                    'class' => 'btn btn-danger', // Add Bootstrap class for styling
                    'onclick' => "return confirm('Are you sure you want to delete this review?');"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}