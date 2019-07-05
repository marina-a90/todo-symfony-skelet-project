<?php
// src/Entity/User.php

namespace App\Entity\User;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class User extends BaseUser
{
    use TimestampableEntity;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        parent::__construct();
    }
}