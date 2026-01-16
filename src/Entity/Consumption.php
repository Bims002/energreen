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
}