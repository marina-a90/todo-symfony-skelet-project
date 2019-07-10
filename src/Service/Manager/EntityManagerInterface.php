<?php

namespace App\Service\Manager;

/**
 * Interface EntityManagerInterface
 */
interface EntityManagerInterface
{
    /**
     * Create new entity.
     *
     * @return mixed
     */
    public function create();

    /**
     * Find entity by id.
     *
     * @param int $id Entity identifier
     *
     * @return mixed
     */
    public function find($id);

    /**
     * Find entity by slug.
     *
     * @param $slug string Entity slug
     *
     * @return mixed
     */
    public function findBySlug($slug);


    /**
     * Search entites by given criterias.
     *
     * @param array $filter List of fiels=>value pairs to filter by
     * @param array $order  List of field=>directions pairs to sort by
     * @param int   $limit  Number of entities to return
     * @param int   $offset Number of entity to start from
     *
     * @return mixed
     */
    public function findAll($filter, $order, $limit, $offset);

    /**
     * Search entites by given criterias.
     *
     * @param array $filter List of fiels=>value pairs to filter by
     *
     * @return int
     */
    public function count($filter);

    /**
     * Save entity.
     *
     * @param object $entity Entity to save
     *
     * @return mixed
     */
    public function save($entity);

    /**
     * Delete entity.
     *
     * @param object $entity Entity to delete
     */
    public function delete($entity);
}