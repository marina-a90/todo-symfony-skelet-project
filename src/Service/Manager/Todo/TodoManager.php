<?php

namespace App\Service\Manager\Todo;

use App\Service\Manager\BaseEntityManager;
use Doctrine\ORM\EntityManager;

/**
 * Class TodoManager
 */
class TodoManager extends BaseEntityManager
{
    /**
     * TodoManager constructor.
     *
     * @param EntityManager  $entityManager
     * @param string         $class
     */
    public function __construct(EntityManager $entityManager, $class)
    {
        parent::__construct($entityManager, $class);
    }

    public function findTodoById($id)
    {
        return $this->findOneBy(array('id' => $id));
    }

    public function findIncompleteTodos() {
        return $this->findBy(array('isDone' => false));
    }
}