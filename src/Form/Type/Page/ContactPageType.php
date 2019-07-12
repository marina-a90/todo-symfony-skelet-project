<?php

namespace App\Form\Type\Page;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 *  Class ContactPageType
 */

// not implemented yet
class ContactPageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'required' => true,
                'constraints' => array(
                    new NotNull(array(
                        "message" => "form.validation.not_null"
                    ))
                )
            ))
            ->add('email', EmailType::class, array(
                'required' => true,
                'constraints' => array(
                    new NotNull(array(
                        "message" => "form.validation.not_null"
                    )),
                    new Email(array(
                        'message' => 'The email "{{ value }}" is not a valid email.',
                    ))
                )
            ))
            ->add('subject', TextType::class, array(
                'required' => true,
                'constraints' => array(
                    new NotNull(array(
                        "message" => "form.validation.not_null"
                    ))
                )
            ))
            ->add('message', TextType::class, array(
                'required' => true,
                'constraints' => array(
                    new NotNull(array(
                        "message" => "form.validation.not_null"
                    ))
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'csrf_protection' => false,
            'translation_domain' => 'AppUser',
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
