<?php

namespace App\Admin;

use App\Entity\User\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

/**
 * Class UserAdmin
 */
class UserAdmin extends AbstractAdmin
{
    protected $datagridValues = array(

        // display the first page (default = 1)
        '_page' => 1,

        // reverse order (default = 'ASC')
        '_sort_order' => 'DESC',

        // name of the ordered field (default = the model's id field, if any)
        '_sort_by' => 'id',
    );



    /**
     * { @inheritdoc }
     */
    protected function configureFormFields(FormMapper $formMapper)
    {

        $subject = $this->getSubject();
        $countryCodes = $this->getConfigurationPool()
            ->getContainer()->get('app.handler.resource_handler')->getCountryCodes();

        $formMapper

            ->with('Basic Details', ['class' => 'col-md-8', 'box_class' => 'box box-secondary'])
                ->add('name')
                ->add('email')
                ->add('username')
            ->end()


            ->with('User Roles', ['class' => 'col-md-8', 'box_class' => 'box box-secondary'])
                ->add('roles', ChoiceType::class, array(
                    'choices' => array(
                        'User' => 'ROLE_USER',
                        'Admin' => 'ROLE_ADMIN',
                        'Super Admin' => 'ROLE_SUPER_ADMIN'
                    ),
                    'expanded' => true,
                    'multiple' => true,
                    'required' => true
                ))
            ->end()


            ->with('Enable/Disable User', ['class' => 'col-md-8', 'box_class' => 'box box-secondary'])
                ->add('enabled')
            ->end()
            ;

        $formMapper
            ->with('User Password', ['class' => 'col-md-8', 'box_class' => 'box box-secondary'])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Password',
                ],
                'second_options' => [
                    'label' => 'Confirm Password',
                ],
                'required' => false,
                'translation_domain' => "admin",
                'invalid_message' => 'fos_user.password.mismatch',
            ])
            ->end()
            ->end();


    }

    /**
     * { @inheritdoc }
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $countryCodes = $this->getConfigurationPool()
            ->getContainer()->get('app.handler.resource_handler')->getCountryCodes();

        $datagridMapper
            ->add('email')
            ->add('username')
            ->add('name', null, ['label' => 'Name'])
            ->add(
                'profile.country',
                'doctrine_orm_choice',
                array('label' => "Country"),
                ChoiceType::class,
                array(
                    'choices' => array(
                        $countryCodes               )
                )
            )
        ;
    }

    /**
     * { @inheritdoc }
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')


            ->add('email')
            //->add('username')
            ->add('name', null, ['label' => 'Name', 'editable' => true, 'template' => 'admin/listFields/username.html.twig'])
            ->add('username')


            ->add('profile.country', null, [
                'template' => 'admin/listFields/fullCountryName.html.twig',
                'label' => 'Country'
            ])

            ->add('roles', null, [
                'template' => 'admin/listFields/roles.html.twig',
                'label' => 'Roles'
            ])

            //->add('deleted')
            ->add('enabled', null, ['label' => "Enabled"])

            ->add('createdAt', null, ['label' => 'Date of registration'])


            ->add('_action', 'actions', [
                'actions' => [
                    'edit' => [],
                    'show' => [],
                ]
            ])
        ;
    }

    /**
     * { @inheritdoc }
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {

        $subject = $this->getSubject();

        if (in_array("ROLE_USER", $subject->getRoles()) && count($subject->getRoles()) === 1) {
            $showMapper
                ->with('User SnGroupMember Info ', ['class' => 'col-md-7', 'box_class' => 'box box-secondary']);
        } else {
            $showMapper
                ->with('User Admin Info ', ['class' => 'col-md-7', 'box_class' => 'box box-secondary']);
        }

        $showMapper
                ->add('id')
                ->add('email')
                ->add('username')
                ->add('name')
                ->add('profile.country', null, [
                    'label' => 'Country',
                    'template' => 'admin/showFields/fullCountryName.html.twig'
                ])
            ->end()


        ->with('User Info ', ['class' => 'col-md-5', 'box_class' => 'box box-secondary'])

        ->add('createdAt')
        ->add('lastLogin')
        ->add('enabled')
        ->end()
        ;
    }

    /**
     * {@inheritdoc }
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        //$collection->remove('edit');
        $collection->remove('delete');
    }

//    /**
//     * { @inheritdoc }
//     */
//    public function prePersist($object) {
//        parent::prePersist($object);
//        $this->updateUser($object);
//    }
//
//    /**
//     * { @inheritdoc }
//     */
//    public function preUpdate($object) {
//        parent::preUpdate($object);
//        $this->updateUser($object);
//    }

    /**
     * { @inheritdoc }
     */
    public function prePersist($object)
    {

        if ($object instanceof User){
            if ($object->isEnabled() === true){
                $object->setConfirmationToken(null);
            }
        }

        parent::prePersist($object);
    }

    /**
     * { @inheritdoc }
     */
    public function preUpdate($object)
    {
        if ($object instanceof User){
            if ($object->isEnabled() === true){
                $object->setConfirmationToken(null);
            }
        }

        parent::preUpdate($object);
    }

    /**
     * Update user
     *
     * @param User $u
     */
    public function updateUser(User $u) {
        $um = $this->getConfigurationPool()->getContainer()->get('fos_user.user_manager');
        $um->updateUser($u, false);
    }
}