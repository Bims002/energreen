<?php

namespace App\Entity;

use App\Repository\ApplianceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApplianceRepository::class)]
class Appliance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(nullable: false)]
    private ?int $power = null;

    #[ORM\Column(nullable: false)]
    private ?float $usage_appliance = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $mode = null;

    #[ORM\Column(nullable: false)]
    private ?float $duration = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPower(): ?int
    {
        return $this->power;
    }

    public function setPower(?int $power): static
    {
        $this->power = $power;

        return $this;
    }

    public function getUsageAppliance(): ?float
    {
        return $this->usage_appliance;
    }

    public function setUsageAppliance(?float $usage_appliance): static
    {
        $this->usage_appliance = $usage_appliance;

        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(string $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    public function getDuration(): ?float
    {
        return $this->duration;
    }

    public function setDuration(float $duration): static
    {
        $this->duration = $duration;

        return $this;
    }
}
