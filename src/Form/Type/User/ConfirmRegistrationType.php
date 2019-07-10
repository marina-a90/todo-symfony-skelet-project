<?php

namespace App\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ConfirmRegistrationType
 */
class ConfirmRegistrationType extends AbstractType
{
    /**
     * { @inheritdoc }
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('client_id')
            ->add('token')
        ;
    }

    /**
     * { @inheritdoc }
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'AppUser',
            'data_class' => null,
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