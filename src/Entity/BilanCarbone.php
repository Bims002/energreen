<?php

namespace App\Entity;

use App\Repository\BilanCarboneRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BilanCarboneRepository::class)]
class BilanCarbone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $logement = null;

    #[ORM\Column]
    private ?float $numerique = null;

    #[ORM\Column]
    private ?float $electromenager = null;

    #[ORM\Column]
    private ?float $alimentation = null;

    #[ORM\Column]
    private ?float $transports = null;

    #[ORM\Column]
    private ?float $textile = null;

    #[ORM\Column]
    private ?float $total = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'bilansCarbone')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $utilisateur = null;

    public function __construct()
    {
        // Initialise la date automatiquement à la création de l'objet
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogement(): ?float
    {
        return $this->logement;
    }

    public function setLogement(float $logement): static
    {
        $this->logement = $logement;
        return $this;
    }

    public function getNumerique(): ?float
    {
        return $this->numerique;
    }

    public function setNumerique(float $numerique): static
    {
        $this->numerique = $numerique;
        return $this;
    }

    public function getElectromenager(): ?float
    {
        return $this->electromenager;
    }

    public function setElectromenager(float $electromenager): static
    {
        $this->electromenager = $electromenager;
        return $this;
    }

    public function getAlimentation(): ?float
    {
        return $this->alimentation;
    }

    public function setAlimentation(float $alimentation): static
    {
        $this->alimentation = $alimentation;
        return $this;
    }

    public function getTransports(): ?float
    {
        return $this->transports;
    }

    public function setTransports(float $transports): static
    {
        $this->transports = $transports;
        return $this;
    }

    public function getTextile(): ?float
    {
        return $this->textile;
    }

    public function setTextile(float $textile): static
    {
        $this->textile = $textile;
        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }
}