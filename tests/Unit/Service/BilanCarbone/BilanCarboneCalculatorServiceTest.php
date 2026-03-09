<?php

namespace App\Tests\Unit\Service\BilanCarbone;

use App\Service\BilanCarbone\BilanCarboneCalculatorService;
use PHPUnit\Framework\TestCase;

class BilanCarboneCalculatorServiceTest extends TestCase
{
    private BilanCarboneCalculatorService $service;

    protected function setUp(): void
    {
        $this->service = new BilanCarboneCalculatorService();
    }

    public function testCalculateScoresWithMinimalData(): void
    {
        $data = [
            'surface' => 50,
            'occupant' => 1,
            'regime_alimentaire' => 2000, // kg CO2 / an
        ];

        $scores = $this->service->calculateScores($data);

        $this->assertArrayHasKey('total', $scores);
        $this->assertGreaterThan(0, $scores['logement']);
        $this->assertEquals(2000, $scores['alimentation']);
    }

    public function testCalculateScoresComplex(): void
    {
        $data = [
            'surface' => 100,
            'isolation_etat' => 1.2, // mauvaise isolation
            'energie_principale' => 0.2, // fioul par exemple
            'occupant' => 2,
            'qty_smartphone' => 2,
            'heures_streaming' => 10,
            'km_voiture' => 10000,
            'vehicule_moteur' => 0.2, // essence
            'regime_alimentaire' => 1500,
        ];

        $scores = $this->service->calculateScores($data);

        // Chauffage : (100 * 110 * 1.2 * 0.2) / 2 = 1320
        $this->assertEquals(1320, $scores['logement']);

        // Numérique : (2 * 80) * 1 + (10 * 0.05 * 365) + 0 + 0 = 160 + 182.5 = 342.5
        $this->assertEquals(342.5, $scores['numerique']);

        // Transports : (10000 * 0.2 * 1 * 1) + 0 + 0 ... = 2000
        $this->assertEquals(2000, $scores['transports']);

        $this->assertGreaterThan(4000, $scores['total']);
    }

    public function testCalculateScoresZeroOccupantDefaultsToOne(): void
    {
        $data = [
            'surface' => 100,
            'occupant' => 0,
        ];

        $scores = $this->service->calculateScores($data);

        // Chauffage sans diviser par zero : 100 * 110 * 1 * 0.052 = 572
        $this->assertEquals(572, $scores['logement']);
    }
}
