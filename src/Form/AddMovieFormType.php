<?php


namespace App\Form;

use App\Entity\Movie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Url;
//This is the transformer class in the Services folder
use App\Services\CommaSeparatedToArrayTransformer;

/**
 * This class is used to create a form for entering a movie.
 */
class AddMovieFormType extends AbstractType
{
    private CommaSeparatedToArrayTransformer $transformer;

    // Inject the transformer service into the class
    public function __construct(CommaSeparatedToArrayTransformer $transformer)

    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('runningTime', IntegerType::class,
                ['required' => false])
            ->add('imdbId', TextType::class,
                ['required' => false])
            ->add('overview', TextareaType::class, [
                'required' => false,
                'attr' => ['maxlength' => 250]
            ])
            // comma-separated strings
            ->add('directors', TextType::class, [
                'required' => false,
            ])
            ->add('actors', TextType::class, [
                'required' => false,
            ])

            //image file upload
            ->add('poster', FileType::class, [
                'label' => 'Movie Poster (Image file)',
                // This field does not directly map to the Movie entity's field because we are storing the image in the public/uploads directory and storing the URL in the database
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '1024k',
                        'maxSizeMessage' => 'The image cannot be larger than 1MB.',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'File has to be jpg, png or gif).',
                    ]),
                ],
            ]);


        //Add the transformer to the directors and actors fields to convert the comma-separated string to an array and pass it to the entity
        //The transformer is used to convert the comma-separated string to an array and vice versa
        $builder->get('directors')->addModelTransformer($this->transformer);
        $builder->get('actors')->addModelTransformer($this->transformer);



        // Add a field for the poster URL
        $builder->add('posterUrl', TextType::class, [
            'mapped' => false,
            'required' => false,
            'attr' => ['placeholder' => 'Poster URL'],
            'constraints' => [
                new Url([
                    'message' => 'Please enter a valid URL',
                ]),
            ],
        ]);
    }


    /**
     * This method is called after the form is submitted and validated.
     * It is used to transform the form data into a Movie object.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Movie::class,
        ]);
    }
}