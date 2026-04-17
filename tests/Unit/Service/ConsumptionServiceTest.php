<?php

namespace App\Tests\Unit\Service;

use App\Entity\Consumption;
use App\Entity\User;
use App\Service\Consumption\ConsumptionService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class ConsumptionServiceTest extends TestCase
{
    public function testGenerateElectricSuggestionsAboveAverage(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $service = new ConsumptionService($entityManager);

        $consumption = new Consumption();
        $consumption->setTotalKwh(450);

        $suggestions = $service->generateElectricSuggestions($consumption);

        $this->assertArrayHasKey('Consommation', $suggestions);
        $this->assertStringContainsString('au-dessus de la moyenne', $suggestions['Consommation']);
    }

    public function testGenerateElectricSuggestionsBelowAverage(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $service = new ConsumptionService($entityManager);

        $consumption = new Consumption();
        $consumption->setTotalKwh(300);

        $suggestions = $service->generateElectricSuggestions($consumption);

        $this->assertArrayHasKey('Consommation', $suggestions);
        $this->assertStringContainsString('maîtrisée', $suggestions['Consommation']);
    }

    public function testSaveConsumption(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(EntityRepository::class);

        $entityManager->method('getRepository')->willReturn($repository);
        $repository->method('findOneBy')->willReturn(null);

        $service = new ConsumptionService($entityManager);

        $user = new User();
        $totalKwh = 100.0;
        $totalPrice = 20.0;

        // On vérifie que persist est appelé au moins deux fois (archive + consumption)
        $entityManager->expects($this->exactly(2))->method('persist');
        $entityManager->expects($this->once())->method('flush');

        $result = $service->saveConsumption($user, $totalKwh, $totalPrice);

        $this->assertInstanceOf(Consumption::class, $result);
        $this->assertEquals($totalKwh, $result->getTotalKwh());
        $this->assertEquals($totalPrice, $result->getEstimatedPrice());
    }
}
