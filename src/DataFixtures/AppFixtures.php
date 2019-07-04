<?php

namespace App\DataFixtures;

use App\Entity\Todo\Todo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
//
//        $manager->flush();

        for ($i = 0; $i < 10; $i++) {
            $todo = new Todo();
            $todo->setTodo('Todo '.$i);
            $todo->setIsDone(mt_rand(0, 1));
            $manager->persist($todo);
        }

        $manager->flush();
    }
}
