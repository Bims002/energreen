<?php

namespace App\Entity;

use App\Repository\ConsumptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsumptionRepository::class)]
class Consumption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $past_consumption = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $billing_date = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    // --- NOUVELLES COLONNES ---

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $total_kwh = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $estimated_price = null;

    // --- MÉTHODES EXISTANTES ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPastConsumption(): ?float
    {
        return $this->past_consumption;
    }

    public function setPastConsumption(float $past_consumption): static
    {
        $this->past_consumption = $past_consumption;
        return $this;
    }

    public function getBillingDate(): ?\DateTimeInterface
    {
        return $this->billing_date;
    }

    public function setBillingDate(\DateTimeInterface $billing_date): static
    {
        $this->billing_date = $billing_date;
        return $this;
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

    // --- NOUVEAUX GETTERS ET SETTERS ---

    public function getTotalKwh(): ?float
    {
        return $this->total_kwh;
    }

    public function setTotalKwh(?float $total_kwh): static
    {
        $this->total_kwh = $total_kwh;
        return $this;
    }

    public function getEstimatedPrice(): ?float
    {
        return $this->estimated_price;
    }

    public function setEstimatedPrice(?float $estimated_price): static
    {
        $this->estimated_price = $estimated_price;
        return $this;
    }
}