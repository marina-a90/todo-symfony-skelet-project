<?php

namespace App\tests\Entity\User;

use App\Entity\User\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetUsername() {
        $user = new User();
        $user->setUsername('ime');

        $this->assertEquals($user->getUsername(), 'ime');
    }

    public function testGetEmail() {
        $user = new User();
        $user->setEmail('ime@email.com');

        $this->assertEquals($user->getEmail(), 'ime@email.com');
    }
}