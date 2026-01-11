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

    #[ORM\Column(nullable: false)]
    private ?int $past_consumption = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    private ?\DateTime $billing_date = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'consumptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPastConsumption(): ?int
    {
        return $this->past_consumption;
    }

    public function setPastConsumption(int $past_consumption): static
    {
        $this->past_consumption = $past_consumption;

        return $this;
    }

    public function getBillingDate(): ?\DateTime
    {
        return $this->billing_date;
    }

    public function setBillingDate(\DateTime $billing_date): static
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
