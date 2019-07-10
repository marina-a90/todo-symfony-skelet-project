<?php

namespace App\Service\Manager\User;

use App\Service\Manager\BaseEntityManager;
use FOS\UserBundle\Doctrine\UserManager as FosUserManager;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserInterface;
/**
 * Class UserManager
 */
class UserManager extends BaseEntityManager
{
    /**
     * @var FosUserManager
     */
    private $fosUserManager;
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * UserManager constructor.
     *
     * @param EntityManager  $entityManager
     * @param string         $class
     * @param FosUserManager $fosUserManager
     */
    public function __construct(EntityManager $entityManager, $class, FosUserManager $fosUserManager)
    {
        parent::__construct($entityManager, $class);

        $this->fosUserManager = $fosUserManager;
        $this->entityManager = $entityManager;
    }

    /**
     * Update user
     *
     * @param UserInterface $user
     * @param bool          $andFlush
     */
    public function update(UserInterface $user, $andFlush = true)
    {
        $this->fosUserManager->updateUser($user, $andFlush);
    }

    /**
     * Find user by confirmation token
     *
     * @param string $token
     *
     * @return UserInterface
     */
    public function findByToken($token)
    {
        return $this->fosUserManager->findUserByConfirmationToken($token);
    }

    /**
     * Find user by username or email
     *
     * @param string $usernameOrEmail
     *
     * @return UserInterface
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        return $this->fosUserManager->findUserByUsernameOrEmail($usernameOrEmail);
    }

    /**
     * Find user by token - only for public route
     *
     * @param string $oauthToken
     *
     * @return UserInterface
     */
    public function findUserByOauthAccessToken($oauthToken)
    {
        return $this->em->getRepository($this->class)->getOAuthAccessToken($oauthToken);
    }

    /**
     * @param int   $offset
     * @param int   $limit
     * @param array $sortBy
     * @param string $direction
     *
     * @return array
     */
    public function getMembers($offset = 0, $limit = 10, $sortBy = 'name', $direction = 'ASC')
    {
        $results = $this->em->getRepository($this->class)->getMembers($offset, $limit, $sortBy, $direction);

        return $results;
    }

    /**
     * For messages
     *
     * @param $id
     * @return null|object
     */
    public function findActiveById($id)
    {
        return $this->em->getRepository($this->class)->findOneBy([
            'id' => $id,
            'enabled' => true
        ]);
    }

    public function getAllNotActivatedOneDay()
    {
        return $this->em->getRepository($this->class)->getAllNotActivatedOneDay();
    }


}
