<?php

namespace App\Entity;

use App\Repository\ArchiveConsumptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArchiveConsumptionRepository::class)]
class ArchiveConsumption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $total_kwh = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $estimated_price = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $archived_at = null;

    public function __construct()
    {
        $this->archived_at = new \DateTimeImmutable();
    }

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getTotalKwh(): ?float
    {
        return $this->total_kwh;
    }
    public function setTotalKwh(float $total_kwh): static
    {
        $this->total_kwh = $total_kwh;
        return $this;
    }

    public function getEstimatedPrice(): ?float
    {
        return $this->estimated_price;
    }
    public function setEstimatedPrice(float $estimated_price): static
    {
        $this->estimated_price = $estimated_price;
        return $this;
    }

    public function getArchivedAt(): ?\DateTimeImmutable
    {
        return $this->archived_at;
    }

    public function setArchivedAt(\DateTimeImmutable $archived_at): static
    {
        $this->archived_at = $archived_at;

        return $this;
    }
}