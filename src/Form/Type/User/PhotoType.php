<?php

namespace App\Form\Type\User;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Utils\Media\ApiUploadedFile;
use Sonata\MediaBundle\Form\Type\MediaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PhotoType
 */
class PhotoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('photo', MediaType::class, [
                'required' => false,
                'provider' => 'sonata.media.provider.image',
                'context'  => 'user_photo',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {

            $data = $event->getData();
            // if image is base64 content
            if (!($data['photo']['binaryContent'] instanceof UploadedFile)) {
                $file = new ApiUploadedFile($data['photo']['binaryContent']);
                $data['photo']['binaryContent'] = $file;
            }

            $event->setData($data);
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            /**@var Media $photo */
            $photo = $data->getPhoto();

            if ($photo) {
                if (!in_array($photo->getContentType(), array('image/jpg', 'image/jpeg', 'image/png', 'image/x-png'))) {
                    $form["photo"]->addError(new FormError('app.user.photo.validation.mime_type'));
                }

                if ($photo->getSize() > 20971520) {
                    $form["photo"]->addError(new FormError('app.user.photo.validation.size'));
                }
            } else {
                $form["photo"]->addError(new FormError('app.user.photo.validation.not_null'));
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\User\User',
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