<?php

namespace App\Tests\Unit\Service\User;

use App\Entity\User;
use App\Service\User\UserRegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegistrationServiceTest extends TestCase
{
    public function testRegisterUser(): void
    {
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $service = new UserRegistrationService($passwordHasher, $entityManager);

        $user = new User();
        $plainPassword = 'password123';
        $hashedPassword = 'hashed_password';

        $passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, $plainPassword)
            ->willReturn($hashedPassword);

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($user);

        $entityManager->expects($this->once())
            ->method('flush');

        $service->registerUser($user, $plainPassword);

        $this->assertEquals($hashedPassword, $user->getPassword());
    }
}
