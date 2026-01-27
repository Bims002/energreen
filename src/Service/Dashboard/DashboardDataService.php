<?php

namespace App\Service\Dashboard;

use App\Entity\Consumption;
use App\Entity\User;
use App\Repository\ArchiveConsumptionRepository;

class DashboardDataService implements DashboardDataServiceInterface
{
    private float $co2Factor = 0.052;
    public function __construct(
        private ArchiveConsumptionRepository $archiveRepo
    ) {
    }

    public function shouldShowUpdateReminder(?Consumption $latestConsumption): bool
    {
        if (!$latestConsumption) {
            return true;
        }

        $lastDate = $latestConsumption->getBillingDate();
        $now = new \DateTime();
        $interval = $lastDate->diff($now);

        return $interval->days >= 7;
    }

    public function prepareMonthlyChartData(User $user): array
    {
        $archives = $this->archiveRepo->findBy(
            ['user' => $user],
            ['archived_at' => 'ASC'],
            12
        );

        $labels = [];
        $data = [];

        foreach ($archives as $archive) {
            $labels[] = $archive->getArchivedAt()->format('d/m');
            $data[] = $archive->getTotalKwh();
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    public function calculateCO2Emissions(?float $kwh): float
    {
        $kwh = $kwh ?? 0.0; // Force à 0 si null
        return $kwh * $this->co2Factor;
    }
}
