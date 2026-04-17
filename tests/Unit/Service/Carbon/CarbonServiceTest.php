<?php

namespace App\Tests\Unit\Service\Carbon;

use App\Entity\BilanCarbone;
use App\Service\Carbon\CarbonService;
use PHPUnit\Framework\TestCase;

class CarbonServiceTest extends TestCase
{
    private CarbonService $carbonService;

    protected function setUp(): void
    {
        $this->carbonService = new CarbonService();
    }

    public function testCalculateCarbonGrade(): void
    {
        $this->assertEquals('A', $this->carbonService->calculateCarbonGrade(5000)['label']);
        $this->assertEquals('B', $this->carbonService->calculateCarbonGrade(7000)['label']);
        $this->assertEquals('C', $this->carbonService->calculateCarbonGrade(8500)['label']);
        $this->assertEquals('D', $this->carbonService->calculateCarbonGrade(9500)['label']);
        $this->assertEquals('E', $this->carbonService->calculateCarbonGrade(10500)['label']);
        $this->assertEquals('F', $this->carbonService->calculateCarbonGrade(12000)['label']);
    }

    public function testGenerateDetailedSuggestionsReturnsEmptyIfNoBilan(): void
    {
        $this->assertEmpty($this->carbonService->generateDetailedSuggestions(null));
    }

    public function testGenerateDetailedSuggestionsReturnsCategories(): void
    {
        $bilan = $this->createMock(BilanCarbone::class);
        $bilan->method('getTransports')->willReturn(2000.0);
        $bilan->method('getAlimentation')->willReturn(1500.0);
        $bilan->method('getLogement')->willReturn(1000.0);
        $bilan->method('getNumerique')->willReturn(500.0);
        $bilan->method('getElectromenager')->willReturn(400.0);
        $bilan->method('getTextile')->willReturn(300.0);

        $suggestions = $this->carbonService->generateDetailedSuggestions($bilan);

        // On s'attend à avoir les 3 catégories les plus gourmandes
        $this->assertCount(3, $suggestions);
        $this->assertArrayHasKey('Transport', $suggestions);
        $this->assertArrayHasKey('Alimentation', $suggestions);
        $this->assertArrayHasKey('Logement', $suggestions);
    }
}
