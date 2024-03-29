<?php

namespace App\Admin;

use App\Entity\Pages\ContactPage;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

/**
 * Class ContactPageAdmin
 */
class ContactPageAdmin extends AbstractAdmin
{
    /**
     * { @inheritdoc }
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('id')
            ->add('email', EmailType::class)
            ->add('location')
            ->add('phone')
        ;
    }

    /**
     * { @inheritdoc }
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper

            ->addIdentifier('id', null, [
                'header_style' => 'width: 5%; text-align: center',
                'row_align' => 'center'
            ])
            ->addIdentifier('email')

            ->add('location')
            ->add('phone')

            ->add('_action', 'actions', [
                'header_style' => 'width: 15%',
                'actions' => [
                    'edit' => [],
                    'show' => []
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
            ->add('email')
            ->add('location')
            ->add('phone')
        ;
    }

    public function toString($object)
    {
        return $object instanceof ContactPage ? $object->getId() : 'ContactPage';
    }
}