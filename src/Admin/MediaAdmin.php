<?php

namespace App\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\Admin\ORM\MediaAdmin as Admin;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class MediaAdmin
 */
class MediaAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    protected function configureFormFields( FormMapper $formMapper ) {
        $media = $this->getSubject();

        if ( ! $media ) {
            $media = $this->getNewInstance();
        }

        if ( ! $media || ! $media->getProviderName() ) {
            return;
        }

        $formMapper->add( 'providerName', HiddenType::class );

        $formMapper->getFormBuilder()->addModelTransformer( new ProviderDataTransformer( $this->pool, $this->getClass() ), true );

        $provider = $this->pool->getProvider( $media->getProviderName() );

        if ( $media->getId() ) {
            $provider->buildEditForm( $formMapper );
        } else {
            $provider->buildCreateForm( $formMapper );
        }

    }
}
