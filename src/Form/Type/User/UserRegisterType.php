<?php

namespace App\Form\Type\User;

use Doctrine\Bundle\DoctrineBundle\Registry;
use App\Entity\User\User;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class UserRegisterType
 */
class UserRegisterType extends MasterUserType
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'required' => true,
                'constraints' => array(
                    new NotNull(array(
                        "message" => "form.profile.terms.validation.not_null"
                    ))
                )
            ])
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => array(
                    new NotNull(array(
                        "message" => "form.profile.terms.validation.not_null"
                    ))
                )
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => array(
                    new NotNull(array(
                        "message" => "form.profile.terms.validation.not_null"
                    ))
                )
            ])
            ->add('password', TextType::class, [
                'required' => true,
                'constraints' => array(
                    new NotNull(array(
                        "message" => "form.profile.terms.validation.not_null"
                    ))
                )
            ])
            ->add('terms', CheckboxType::class, [
                'required' => true,
                'constraints' => array(
                    new NotNull(array(
                        "message" => "form.profile.terms.validation.not_null"
                    ))
                )
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            /**@var User $data */
            $data = $event->getData();
            $userRepository = $this->doctrine->getRepository(User::class);

            $alreadyRegisterUser = $userRepository->findOneBy(array('email' => $form["email"]->getData()));
            if ($alreadyRegisterUser) {
                $form["email"]->addError(new FormError('app.user.form.email.unique'));
            }

            $alreadyRegisterUsername = $userRepository->findOneBy(array('username' => $form["username"]->getData()));
            if ($alreadyRegisterUsername) {
                $form["username"]->addError(new FormError('app.user.form.username.unique'));
            }

            $checkTerms = $userRepository->findOneBy(array('terms' => $form["terms"]->getData()));
            if ($checkTerms != true) {
                $form["terms"]->addError(new FormError('app.user.form.terms.not_accepted'));
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
//            'translation_domain' => 'AppUser',
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