<?php

namespace App\Form\Type\User;

use Symfony\Component\Form\FormBuilderInterface;


/**
 * Class UserEditType
 */
class UserEditType extends MasterUserType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('username')
            ->remove('email')
            ->remove('plainPassword')
            ->remove('profile')
            ->remove('terms')
            ->remove('country')
        ;
    }
}
