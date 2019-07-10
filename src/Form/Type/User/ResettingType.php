<?php

namespace App\Form\Type\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class ResettingType
 */
class ResettingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.new_password'),
                'second_options' => array('label' => 'form.new_password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
                'required' => true,
                'constraints' => array(
                    new NotNull(array(
                        "message" => "form.user.plainPassword.validation.not_null"
                    )),
                    new Length(array(
                        "min" => 8,
                        "minMessage" => "form.user.plainPassword.validation.min",
                        "max" => 50,
                        "maxMessage" => "form.user.plainPassword.validation.max",
                    ))
                )
            ))
            ->add('token', TextType::class, array(
                'mapped' => false,
                "required" => true,
            ))
            ->add('client_id', TextType::class, array(
                'mapped' => false,
                "required" => true,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\User\User',
            'csrf_token_id' => 'resetting',
            'translation_domain' => 'AppUser'
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
