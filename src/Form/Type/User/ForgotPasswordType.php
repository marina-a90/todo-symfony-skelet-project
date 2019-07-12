<?php

namespace App\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ForgotPasswordType
 */
class ForgotPasswordType extends AbstractType
{
    /**
     * { @inheritdoc }
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, [
            'label' => 'form.email',
            'required' => true,
            'constraints' => [
                new NotBlank(),
                new Email(),
            ],
            'mapped' => false,
        ]);
    }

    /**
     * { @inheritdoc }
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'AppUser',
            'data_class' => null
        ));
    }

    /**
     * { @inheritdoc }
     */
    public function getBlockPrefix()
    {
        return '';
    }
}