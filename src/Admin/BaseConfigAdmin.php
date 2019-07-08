<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

abstract class BaseConfigAdmin extends AbstractAdmin
{
    /**
     * @var string
     */
    protected $baseRoutePattern = '/';

    /**
     * @var bool
     */
    protected $isPrivate = false;

    /**
     * Get name of form schema user for config
     *
     * @return string
     */
    abstract protected function getConfigSchema();

    /**
     * { @inheritdoc }
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clear();

        $collection->add('configure', '/config/' . $this->baseRouteName, [
            'namespace' => $this->baseRouteName,
            'schema' => $this->getConfigSchema(),
            'private' => $this->isPrivate,
        ]);
        $collection->add('list', '/config/' . $this->baseRouteName, [
            'namespace' => $this->baseRouteName,
            'schema' => $this->getConfigSchema(),
            'private' => $this->isPrivate,
        ]);
    }

    /**
     * { @inheritdoc }
     */
    public function getDashboardActions()
    {
        $actions = [
            'configure' => [
                'label' => 'link_configure',
                'translation_domain' => 'admin',
                'url' => $this->generateUrl('configure'),
                'icon' => 'cog',
            ]
        ];

        return $actions;
    }
}