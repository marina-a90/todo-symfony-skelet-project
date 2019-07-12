<?php

namespace App\Form\Type\User;

use Doctrine\Bundle\DoctrineBundle\Registry;
use App\Entity\User\User;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
        parent::buildForm($builder, $options);

        $builder
            ->add('terms', CheckboxType::class, [
                'required' => true,
                'constraints' => array(
                    new NotNull(array(
                        "message" => "form.user.terms.not_null"
                    ))
                )
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            $userRepository = $this->doctrine->getRepository(User::class);

            $alreadyRegisterUser = $userRepository->findOneBy(array('email' => $form["email"]->getData()));
            if ($alreadyRegisterUser) {
                $form["email"]->addError(new FormError('app.user.email.unique'));
            }

            $alreadyRegisterUsername = $userRepository->findOneBy(array('username' => $form["username"]->getData()));
            if ($alreadyRegisterUsername) {
                $form["username"]->addError(new FormError('app.user.username.unique'));
            }

            $checkTerms = $userRepository->findOneBy(array('terms' => $form["terms"]->getData()));
            if ($checkTerms != true) {
                $form["terms"]->addError(new FormError('app.user.terms.validation.not_null'));
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