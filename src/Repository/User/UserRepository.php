<?php

namespace App\Repository\User;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 */
class UserRepository extends EntityRepository
{
    /**
     * Find user by access token -  only for additional registration
     * include not finished register user with oauth2 token
     *
     * @param $token
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOAuthAccessToken($token)
    {
        $currentDate = new \DateTime();
        $currentDateTimestamp = $currentDate->getTimestamp();

        $sql = "SELECT a, u
                FROM App:OAuth\AccessToken a
                INNER JOIN a.user u
                WHERE a.token = :token
                AND a.expiresAt > :timestamp"
        ;

        $result = $this->getEntityManager()
            ->createQuery($sql)
            ->setParameter("token", $token)
            ->setParameter("timestamp", $currentDateTimestamp)
            ->getOneOrNullResult();

        if ($result) {
            return $result->getUser();
        }

        return ($result) ? $result->getUser() : null;
    }

    /**
     * @param int   $offset
     * @param int   $limit
     * @param array $sortBy
     *
     * @return array
     */
    public function getMembers($offset = 0, $limit = 10, $sortBy = 'name', $direction = 'ASC')
    {
        $results = $this->createQueryBuilder('u')
            ->where('u.enabled = TRUE')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('u.' . $sortBy, $direction)
            ->getQuery()
            ->getResult();

        return $results;
    }

    public function getAllNotActivatedOneDay()
    {
        $oneDayBefore = new \DateTime('-1 day');

        $qb = $this->createQueryBuilder('u');
        $qb
            ->where('u.enabled = FALSE')
            ->andWhere('u.createdAt < :oneDay')
            // don't catch already activated (e.g. change password request)
            ->andWhere($qb->expr()->andX(
                $qb->expr()->isNotNull('u.confirmationToken'),
                $qb->expr()->isNull('u.passwordRequestedAt')
            ))
            ->setParameter('oneDay', $oneDayBefore)
        ;

        return $qb->getQuery()->getResult();

    }

    public function getLatestMembers($offset = 0, $limit = 8, $sortBy = 'id', $direction = 'DESC')
    {
        $results = $this->createQueryBuilder('u')
            ->setMaxResults($limit)
            ->orderBy('u.' . $sortBy, $direction)
            ->getQuery()
            ->getResult();

        return $results;
    }
}