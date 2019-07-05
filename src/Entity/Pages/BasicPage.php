<?php

namespace App\Entity\Pages;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\ExclusionPolicy;

/**
 * BasicPage
 *
 * @ORM\Entity
 * @ORM\Table(name="basic_page")
 *
 * @ExclusionPolicy("all")
 */
class BasicPage
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="string", length=255)
     * @ORM\Id
     *
     * @Expose
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     *
     * @Expose
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="intro", type="text")
     *
     * @Expose
     */
    private $intro;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     *
     * @Expose
     */
    private $body;

    /**
     * Set id
     *
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return BasicPage
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getIntro()
    {
        return $this->intro;
    }

    /**
     * @param string $intro
     *
     * @return BasicPage
     */
    public function setIntro($intro)
    {
        $this->intro = $intro;

        return $this;
    }

    /**
     * Set body
     *
     * @param string $body
     *
     * @return BasicPage
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * String representation of BasicPage
     *
     * @return string
     */
    public function __toString()
    {
        return $this->title;
    }
}

