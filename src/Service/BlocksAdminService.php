<?php

namespace App\Service;

use Doctrine\ORM\EntityManager;
use Sonata\BlockBundle\Block\BaseBlockService;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\Templating\EngineInterface;

class BlocksAdminService extends BaseBlockService
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @param string          $name
     * @param EngineInterface $templating
     * @param EntityManager $entityManager
     */
    public function __construct($name, EngineInterface $templating, EntityManager $entityManager)
    {
        parent::__construct($name, $templating);
        $this->entityManager = $entityManager;

    }

    public function getName()
    {
        return 'blocks';
    }

    public function getDefaultSettings()
    {
        return array();
    }

    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
    }

    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {

        // merge settings
        $settings = $blockContext->getSettings();
        $feeds = false;

        // contents
        $users = $this->entityManager->getRepository('App\Entity\User\User')->getLatestMembers();

        $countUsers = $this->entityManager->getRepository('App\Entity\User\User')->count([]);
        $countTodos = $this->entityManager->getRepository('App\Entity\Todo\Todo')->count([]);
        $countPages = $this->entityManager->getRepository('App\Entity\Pages\BasicPage')->count([]);
        $countContactPages = $this->entityManager->getRepository('App\Entity\Pages\ContactPage')->count([]);

        return $this->renderResponse('admin/Dashboard/blocks.html.twig', array(
            'users' => $users,
            'content'  => [
                'user' => [
                    'label' => 'Users',
                    'count' => $countUsers,
                    'image' => 'fa fa-user',
                    'route' => 'admin_app_user_user_list'
                ],
                'todo' => [
                    'label' => 'Todos',
                    'count' => $countTodos,
                    'image' => 'fa fa-building',
                    'route' => 'admin_app_todo_todo_list'
                ],
                'page' => [
                    'label' => 'Pages',
                    'count' => $countPages,
                    'image' => 'fa fa-building',
                    'route' => 'admin_app_pages_basicpage_list'
                ],
                'contact pages' => [
                    'label' => 'Contact Pages',
                    'count' => $countContactPages,
                    'image' => 'fa fa-building',
                    'route' => 'admin_app_pages_contactpage_list'
                ],
            ],
            'feeds'     => $feeds,
            'block'     => $blockContext->getBlock(),
            'settings'  => $settings
        ), $response);
    }
}