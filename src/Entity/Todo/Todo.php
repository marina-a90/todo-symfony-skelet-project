<?php

namespace App\Entity\Todo;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Todo
 *
 * @ORM\Table(name="todo")
 * @ORM\Entity(repositoryClass="App\Repository\Todo\TodoRepository")
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Todo
{
    use TimestampableEntity;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @JMS\Expose()
     * @JMS\Groups({"details", "list"})
     */
    private $todo;

    /**
     * @ORM\Column(type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Groups("details")
     */
    private $isDone = false;

    public function getId()
    {
        return $this->id;
    }

    public function getTodo()
    {
        return $this->todo;
    }

    public function setTodo(string $todo)
    {
        $this->todo = $todo;

        return $this;
    }

    public function getIsDone()
    {
        return $this->isDone;
    }

    public function setIsDone(bool $isDone)
    {
        $this->isDone = $isDone;

        return $this;
    }
}
