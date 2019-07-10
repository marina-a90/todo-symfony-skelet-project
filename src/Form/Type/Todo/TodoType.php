<?php

namespace App\Form\Type\Todo;

use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class TodoType extends AbstractType
{
    public function __construct()
    {
        $this->isDone = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('todo', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Length([
                        "max" => 255,
                        "maxMessage" => "form.validation.max",
                    ])
                ]
            ])
            ->add('isDone'
//                , 'hidden',
//                [
//                'required' => false,
//                'choices' => [
//                    'Yes' => 1,
//                    'No' => 0
//                ],
//                'empty_value' => false
//            ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Todo\Todo',
            'csrf_protection' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return '';
    }
}