<?php

namespace App\Tests\Unit\Service\Alert;

use App\Entity\Consumption;
use App\Service\Alert\AlertService;
use PHPUnit\Framework\TestCase;

class AlertServiceTest extends TestCase
{
    private AlertService $alertService;

    protected function setUp(): void
    {
        $this->alertService = new AlertService();
    }

    public function testGenerateSmartAlertsReturnsEmptyArrayIfNoConsumption(): void
    {
        $this->assertEmpty($this->alertService->generateSmartAlerts(null));
    }

    public function testGenerateSmartAlertsSuccessThreshold(): void
    {
        $consumption = $this->createMock(Consumption::class);
        $consumption->method('getTotalKwh')->willReturn(200.0);

        $alerts = $this->alertService->generateSmartAlerts($consumption);

        $this->assertCount(1, $alerts);
        $this->assertEquals('success', $alerts[0]['type']);
        $this->assertStringContainsString('Consommation Maîtrisée', $alerts[0]['title']);
    }

    public function testGenerateSmartAlertsWarningThreshold(): void
    {
        $consumption = $this->createMock(Consumption::class);
        $consumption->method('getTotalKwh')->willReturn(400.0);
        $consumption->method('getEstimatedPrice')->willReturn(80.0);

        $alerts = $this->alertService->generateSmartAlerts($consumption);

        $this->assertCount(1, $alerts);
        $this->assertEquals('warning', $alerts[0]['type']);
        $this->assertStringContainsString('Gros Consommateur Identifié', $alerts[0]['title']);
    }

    public function testGenerateSmartAlertsDangerThreshold(): void
    {
        // Actuellement buggé dans le code (n'atteint jamais 600)
        $consumption = $this->createMock(Consumption::class);
        $consumption->method('getTotalKwh')->willReturn(700.0);
        $consumption->method('getEstimatedPrice')->willReturn(150.0);

        $alerts = $this->alertService->generateSmartAlerts($consumption);

        $this->assertCount(1, $alerts);
        // On s'attend à 'danger' si on corrige le bug
        // Pour l'instant on va voir ce que ça donne
        $this->assertEquals('danger', $alerts[0]['type'], 'Le seuil danger (>600) devrait être prioritaire ou atteint.');
    }
}
