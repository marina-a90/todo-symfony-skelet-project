<?php
// src/Entity/User.php

namespace App\Entity\User;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 *
 * @JMS\ExclusionPolicy("all")
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
     *
     * @JMS\Expose()
     * @JMS\Groups({"details", "list", "public"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @JMS\Expose()
     * @JMS\Groups({"details", "list", "public"})
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true
     * )
     */
    protected $email;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deleted = 0;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Assert\NotNull
     */
    private $terms = false;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getDeleted()
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getTerms()
    {
        return $this->terms;
    }

    public function setTerms(bool $terms)
    {
        $this->terms = $terms;

        return $this;
    }
}