<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
/**
 * Class BasicPageAdmin
 */
class BasicPageAdmin extends AbstractAdmin
{
    /**
     * { @inheritdoc }
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title')
            ->add('intro')
            ->add('body', CKEditorType::class, [
                'config_name' => 'standard'
            ])
        ;
    }

    /**
     * { @inheritdoc }
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title')
            ->add('intro')
            ->add('body')
        ;
    }

    /**
     * { @inheritdoc }
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper

            ->addIdentifier('id')
            ->addIdentifier('title')

            //->add('intro')
            //->add('body', null, ['editable' => true, 'collapse' => true])

            ->add('intro', null, [
                'editable' => false,
                'template' => 'admin/listFields/intro.html.twig',
                'label' => 'Intro'
            ])

            ->add('body', null, [
                'editable' => false,
                'template' => 'admin/listFields/body.html.twig',
                'label' => 'Body'
            ])

            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ]
            ])
        ;
    }

    /**
     * { @inheritdoc }
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('title')
            ->add('intro')
            ->add('body', CKEditorType::class, [
                'template' => 'SonataAdminBundle:CRUD:show_html.html.twig'
            ])
        ;
    }

    /**
     * {@inheritdoc }
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
    }
}