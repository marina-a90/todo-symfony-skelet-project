<?php

namespace App\EventListener;

use App\Entity\Interfaces\Authorable;
use App\Entity\Payment\Purchase;
use App\Entity\User\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class AuthorableEntityListener
 */
class AuthorableEntityListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * AuthorableEntityListener constructor.
     *
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        if (($entity = $args->getEntity()) && $entity instanceof Authorable) {

            $token = $this->container->get('security.token_storage')->getToken();

            if ($token) {
                $user = $token->getUser();
            } else {
                $user = 'anon.';
            }

            if ($user instanceof User) {
                $entity->setAuthor($user);
            }
        }
    }
}
