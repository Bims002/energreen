<?php

namespace App\Tests\Unit\Service\Dashboard;

use App\Entity\ArchiveConsumption;
use App\Entity\Consumption;
use App\Entity\User;
use App\Repository\ArchiveConsumptionRepository;
use App\Service\Dashboard\DashboardDataService;
use PHPUnit\Framework\TestCase;

class DashboardDataServiceTest extends TestCase
{
    private DashboardDataService $service;
    private $archiveRepo;

    protected function setUp(): void
    {
        $this->archiveRepo = $this->createMock(ArchiveConsumptionRepository::class);
        $this->service = new DashboardDataService($this->archiveRepo);
    }

    public function testShouldShowUpdateReminderReturnsTrueIfNoConsumption(): void
    {
        $this->assertTrue($this->service->shouldShowUpdateReminder(null));
    }

    public function testShouldShowUpdateReminderReturnsTrueIfOld(): void
    {
        $consumption = $this->createMock(Consumption::class);
        $consumption->method('getBillingDate')->willReturn(new \DateTime('-10 days'));

        $this->assertTrue($this->service->shouldShowUpdateReminder($consumption));
    }

    public function testShouldShowUpdateReminderReturnsFalseIfRecent(): void
    {
        $consumption = $this->createMock(Consumption::class);
        $consumption->method('getBillingDate')->willReturn(new \DateTime('-2 days'));

        $this->assertFalse($this->service->shouldShowUpdateReminder($consumption));
    }

    public function testCalculateCO2Emissions(): void
    {
        $this->assertEquals(5.2, $this->service->calculateCO2Emissions(100.0));
        $this->assertEquals(0.0, $this->service->calculateCO2Emissions(null));
    }

    public function testPrepareMonthlyChartData(): void
    {
        $user = $this->createMock(User::class);

        $archive1 = $this->createMock(ArchiveConsumption::class);
        $archive1->method('getArchivedAt')->willReturn(new \DateTimeImmutable('2023-01-01'));
        $archive1->method('getTotalKwh')->willReturn(100.0);

        $archive2 = $this->createMock(ArchiveConsumption::class);
        $archive2->method('getArchivedAt')->willReturn(new \DateTimeImmutable('2023-02-01'));
        $archive2->method('getTotalKwh')->willReturn(150.0);

        $this->archiveRepo->method('findBy')->willReturn([$archive1, $archive2]);

        $result = $this->service->prepareMonthlyChartData($user);

        $this->assertEquals(['01/01', '01/02'], $result['labels']);
        $this->assertEquals([100.0, 150.0], $result['data']);
    }
}
