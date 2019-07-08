<?php

namespace App\Admin;

use App\Entity\Todo\Todo;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class TodoAdmin extends AbstractAdmin
{
    // create
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('todo', TextType::class)
            ->add('isDone', BooleanType::class)
        ;
    }

    // filters
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('todo')
            ->add('isDone')
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }

    // list
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('todo')       // Identifier adds a link
            ->add('isDone')
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }

    // notify the admin of a successful creation - transform object to a string
    public function toString($object)
    {
        return $object instanceof Todo ? $object->getTodo() : 'Todo';
        // shown in the breadcrumb on the create view
    }
}