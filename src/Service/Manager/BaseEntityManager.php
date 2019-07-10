<?php

namespace App\Service\Manager;

use Doctrine\ORM\EntityManager;

/**
 * Class BaseEntityManager
 */
class BaseEntityManager implements EntityManagerInterface
{
    /**
     * Entity manager.
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * Name of the class.
     *
     * @var string
     */
    protected $class;

    /**
     * BaseEntityManager constructor.
     *
     * @param EntityManager $entityManager Doctrine entity manager
     * @param string        $class         Class name
     */
    public function __construct(EntityManager $entityManager, $class)
    {
        $this->em = $entityManager;
        $this->class = $class;
    }

    /**
     * Create new entity.
     *
     * @return mixed
     */
    public function create()
    {
        $class = $this->em
            ->getRepository($this->class)
            ->getClassName();

        return new $class;
    }

    /**
     * Find entity by id.
     *
     * @param int $id Entity identifier
     *
     * @return null|object
     */
    public function find($id)
    {
        return $this->em
            ->getRepository($this->class)
            ->find($id);
    }

    /**
     * Find entity by slug.
     *
     * @param string $slug Entity slug
     *
     * @return null|object
     */
    public function findBySlug($slug)
    {
        return $this->em
            ->getRepository($this->class)
            ->findOneBy([
                'slug' => $slug,
            ]);
    }

    /**
     * Find entity by username.
     *
     * @param string $username
     *
     * @return null|object
     */
    public function findByUsername($username)
    {
        return $this->em
            ->getRepository($this->class)
            ->findOneBy([
                'username' => $username,
            ]);
    }

    /**
     * Search entities by given criteria.
     *
     * @param array $filter List of fiels=>value pairs to filter by
     * @param array $order  List of field=>directions pairs to sort by
     * @param int   $limit  Number of entities to return
     * @param int   $offset Number of entity to start from
     *
     * @return mixed
     */
    public function findAll($filter = [], $order = [], $limit = null, $offset = null)
    {
        return $this->em
            ->getRepository($this->class)
            ->findBy(
                $filter,
                $order,
                $limit,
                $offset
            );
    }

    /**
     * Find one entity by given criteria
     *
     * @param array $criteria
     * @param array $order
     *
     * @return null|object
     */
    public function findOneBy($criteria, $order = null)
    {
        return $this->em
            ->getRepository($this->class)
            ->findOneBy($criteria, $order);
    }

    /**
     * Find entities by given criteria
     *
     * @param array $criteria
     * @param array $order
     *
     * @return null|object
     */
    public function findBy($criteria, $order = null)
    {
        return $this->em
            ->getRepository($this->class)
            ->findBy($criteria, $order);
    }

    /**
     * Search entities by given criteria.
     *
     * @param array $filter List of fields=>value pairs to filter by
     *
     * @return int
     */
    public function count($filter)
    {
        $qb = $this->em
            ->getRepository($this->class)
            ->createQueryBuilder('c');

        $qb->select('count(c.id)');

        foreach ($filter as $key => $value) {
            if ($value === null) {
                $qb->andWhere('c.' . $key . ' is NULL');
            } else {
                if (is_array($value)) {
                    $qb->andWhere('c.' . $key . 'IN (:' . $key . ')');
                } else {
                    $qb->andWhere('c.' . $key . '=:' . $key);
                }

                $qb->setParameter($key, $value);
            }
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Refresh entity
     *
     * @param object $entity
     */
    public function refresh($entity)
    {
        $this->em->refresh($entity);
    }

    /**
     * Save doctrine entity
     *
     * @param object $entity
     * @param bool   $refresh
     *
     * @return mixed|object
     */
    public function save($entity, $refresh = false)
    {
        $this->em->persist($entity);
        $this->em->flush();

        if ($refresh) {
            $this->refresh($entity);
        }

        return $entity;
    }

    /**
     * Delete entity.
     *
     * @param object $entity Entity to delete
     */
    public function delete($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

}