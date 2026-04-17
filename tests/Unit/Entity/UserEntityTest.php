<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserEntityTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $user = new User();

        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertInstanceOf(\DateTimeInterface::class, $user->getCreatedAt());
    }

    public function testEmailGetterSetter(): void
    {
        $user = new User();
        $email = 'test@example.com';

        $user->setEmail($email);
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($email, $user->getUserIdentifier());
    }

    public function testRolesGetterSetter(): void
    {
        $user = new User();
        $roles = ['ROLE_ADMIN'];

        $user->setRoles($roles);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testPasswordGetterSetter(): void
    {
        $user = new User();
        $password = 'hashed_password';

        $user->setPassword($password);
        $this->assertEquals($password, $user->getPassword());
    }
}
