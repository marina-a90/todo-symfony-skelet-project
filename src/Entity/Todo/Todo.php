<?php

namespace App\Entity\Todo;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Todo
 *
 * @ORM\Table(name="todo")
 * @ORM\Entity(repositoryClass="App\Repository\Todo\TodoRepository")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Todo
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $todo;

    /**
     * @ORM\Column(type="boolean")
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
