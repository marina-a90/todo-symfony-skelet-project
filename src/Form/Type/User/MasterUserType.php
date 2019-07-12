<?php

namespace App\Form\Type\User;

use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Abstract Class MasterUserType
 */
abstract class MasterUserType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array(
                'required' => true,
                "constraints" => array(
                    new NotNull(array(
                        "message" => "form.user.username.validation.not_null"
                    )),
                    new Length(array(
                        "min" => 3,
                        "minMessage" => "form.user.username.validation.min",
                        "max" => 25,
                        "maxMessage" => "form.user.username.validation.max",
                    )),
                    new Regex(array(
                        "pattern" => '/^[a-zA-Z0-9_]*$/',
                        "message" => "form.user.username.validation.pattern",
                    ))
                )
            ))
            ->add('email', EmailType::class, array(
                'required' => true,
                "constraints" => array(
                    new NotNull(array(
                        "message" => "form.user.email.validation.not_null"
                    )),
                    new Email()
                )
            ))
            ->add('plainPassword', RepeatedType::class, [
                'required' => true,
                'type' => PasswordType::class,
                'first_options' => ['label' => 'form.password'],
                'second_options' => ['label' => 'form.password_confirmation'],
                'invalid_message' => 'fos_user.password.mismatch',
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
            ])
            ->add('name', TextType::class, array(
                "required" => true,
                'constraints' => array(
                    new NotNull(array(
                        "message" => "form.profile.name.validation.not_null"
                    )),
                    new Length(array(
                        "min" => 2,
                        "minMessage" => "form.profile.name.validation.min",
                        "max" => 25,
                        "maxMessage" => "form.profile.name.validation.max",
                    ))
                )
            ))
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            if ($form->has('username')) {
                $data = $event->getData();

                $username = $data->getUsername();

                if ($username) {
                    if ((strpos($username, 'admin') !== false)) {
                        $form["username"]->addError(new FormError('form.user.username.validation.pattern'));
                    }
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
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