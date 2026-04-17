<?php

namespace App\Tests\Unit\Service;

use App\Entity\BilanCarbone;
use App\Entity\User;
use App\Service\BilanCarboneManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class BilanCarboneManagerTest extends TestCase
{
    public function testArchiveOldBilan(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $manager = new BilanCarboneManager($entityManager);

        $user = new User();
        $bilan = $this->createMock(BilanCarbone::class);
        $bilan->method('getUtilisateur')->willReturn($user);
        $bilan->method('getTotal')->willReturn(150.5);

        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('remove');
        $entityManager->expects($this->once())->method('flush');

        $manager->archiveOldBilan($bilan);
    }
}
